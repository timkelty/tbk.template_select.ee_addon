<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * Template Select
 */
class Tbk_template_select extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'     => 'Template Select',
		'version'  => '1.1',
		'no_lang'  => TRUE
	);

var $default_field_settings = array(
	'templates' => array()
);

var $default_cell_settings = array(
	'templates' => array(),
);

/**
 * Template Groups Multi-select
 *
 * @return string  multi-select HTML
 * @access private
 */
function _templates_select($selected_templates)
{
	global $PREFS, $FFSD, $DSP, $DB;

	// Is MSM enabled?
	$msm = ($PREFS->ini('multiple_sites_enabled') == 'y');

	// Get the current Site ID
	$site_id = $PREFS->ini('site_id');

	$r = $DSP->qdiv('defaultBold', 'Templates');
  
	$sql = "SELECT tg.group_name, t.template_id, t.template_name
				FROM   exp_template_groups tg, exp_templates t
				WHERE  tg.group_id = t.group_id
			  AND    tg.site_id = '".$site_id."' ";
	if (USER_BLOG == TRUE)
	{
		$sql .= "AND tg.group_id = '".$SESS->userdata['tmpl_group_id']."' ";
	}
	else
	{
		$sql .= "AND tg.is_user_blog = 'n' ";
	}
			
	$templates = $DB->query($sql." ORDER BY tg.group_name, t.template_name");
	
	if ($templates->num_rows)
	{
		$r .= $DSP->input_select_header('templates[]', 'y', ($templates->num_rows < 15 ? $templates->num_rows : 15), 'auto');

		if ($msm) $current_site_label = '';

		foreach($templates->result as $template)
		{
			if ($msm AND $template['site_label'] != $current_site_label)
			{
				if ($current_site_label) $r .= '</optgroup>';
				$r .= '<optgroup label="'.$template['site_label'].'">';
				$current_site_label = $template['site_label'];
			}

			$selected = in_array($template['template_id'], $selected_templates) ? 1 : 0;
			$r .= $DSP->input_select_option($template['group_name'] . '/' . $template['template_name'], $template['group_name'] . '/' . $template['template_name'], $selected);
		}

		if ($msm) $r .= '</optgroup>';

		$r .= $DSP->input_select_footer();
	}
	else
	{
		$r .= $DSP->qdiv('highlight', 'No Templates Exist');
	}

	return $r;
}


	/* 
	Added by Brian Litzinger 
	*/
	/**
	 * Display Tag
	 *
	 * @param  array   $params          Name/value pairs from the opening tag
	 * @param  string  $tagdata         Chunk of tagdata between field tag pairs
	 * @param  string  $field_data      Currently saved field value
	 * @param  array   $field_settings  The field's settings
	 * @return string
	 */
	function display_tag($params, $tagdata, $field_data, $field_settings)
	{
		global $TMPL;
		
		$r = '';
		
		if($field_data) 
		{
			if( !is_array($field_data) and $field_data != '' ) {
				$r = $field_data;
			} elseif( $field_data[0] != '' ) {
				$r = ( isset($field_data[0]) ) ? $field_data[0] : '';
			}
		}
		
		return $r;
	}


	/**
	 * Display Field Settings
	 * 
	 * @param  array  $field_settings  The field's settings
	 * @return array  Settings HTML (cell1, cell2, rows)
	 */
	function display_field_settings($field_settings)
	{
		global $FFSD;

		// initialize Fieldframe_SettingsDisplay
		if ( ! isset($FFSD))
		{
			$FFSD = new Fieldframe_SettingsDisplay();
		}

		return array(
			'cell2' => $this->_templates_select($field_settings['templates']),
		);
	}

	/**
	 * Display Cell Settings
	 * 
	 * @param  array  $cell_settings  The cell's settings
	 * @return array  Settings HTML
	 */
	function display_cell_settings($field_settings)
	{
		global $DSP;
		
		$r = '<label class="itemWrapper">'
		   .   $this->_templates_select($field_settings['templates'])
		   . '</label>';
		return $r;
	}

	/**
	 * Display Field
	 * 
	 * @param  string  $field_name      The field's name
	 * @param  mixed   $field_data      The field's current value
	 * @param  array   $field_settings  The field's settings
	 * @return string  The field's HTML
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
    	global $FFSD;

		// initialize Fieldframe_SettingsDisplay
		if ( ! isset($FFSD))
		{
			$FFSD = new Fieldframe_SettingsDisplay();
		}
		
		/* 
		Added by Brian Litzinger 
		It was returning the array key, not the path to the template
		*/
		$new_settings[''] = '--';
		foreach($field_settings['templates'] as $key => $value)
		{
			$new_settings[$value] = $value;
		}
		
		return $FFSD->select($field_name, $field_data, $new_settings);
	}

	/**
	 * Display Cell
	 * 
	 * @param  string  $cell_name      The cell's name
	 * @param  mixed   $cell_data      The cell's current value
	 * @param  array   $cell_settings  The cell's settings
	 * @return string  The cell's HTML
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{
		return $this->display_field($cell_name, $cell_data, $cell_settings);
	}

	/**
	 * Label
	 *
	 * @param  array   $params          Name/value pairs from the opening tag
	 * @param  string  $tagdata         Chunk of tagdata between field tag pairs
	 * @param  string  $field_data      Currently saved field value
	 * @param  array   $field_settings  The field's settings
	 * @return string  relationship references
	 */
	function label($params, $tagdata, $field_data, $field_settings)
	{
		return $field_settings['label'];
	}

}