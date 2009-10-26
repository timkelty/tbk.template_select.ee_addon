<?php

if ( ! defined('EXT')) exit('Invalid file request');

/**
 * Freeform Data Select
 */

class Tbk_template_select extends Fieldframe_Fieldtype {

  /**
   * Fieldtype Info
   * @var array
   */
	var $info = array(
		'name'     => 'Template Select',
		'version'  => '1.2',
	);
	var $requires = array(
      'ff'        => '1.3.4',
  );
  
	var $default_site_settings = array(
  	'null_option' => '--',
  );
  var $default_field_settings = array(
  	'templates' => array(),
  	'all_templates' => 'n',
  );
  var $default_cell_settings = array(
  	'templates' => array(),
  	'all_templates' => 'n',
  );
  
  /**
   * Template Groups Multi-select
   *
   * @return string  multi-select HTML
   * @access private
   */
  function _templates_select($selected_templates, $name, $multi, $include_null)
  {
  	global $LANG, $PREFS, $DSP, $DB, $SESS;

    $LANG->fetch_language_file('tbk_template_select');

  	// Get the current Site ID
  	$site_id = $PREFS->ini('site_id');
  	
  	$r = '';
  	$sql = "SELECT tg.group_name, t.template_name
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
  		$r .= $DSP->input_select_header($name, $multi, ($templates->num_rows < 15 ? $templates->num_rows : 15), 'auto');
      $r .= ($include_null != 'y' && !$multi) ? $DSP->input_select_option('', $this->site_settings['null_option']) : '';

  		foreach($templates->result as $template)
  		{
  		  if (is_array($selected_templates)) {
    			$selected = in_array($template['group_name'] . '/' . $template['template_name'], $selected_templates) ? 'y' : '';
  		  }
  		  else {
    			$selected = ($template['group_name'] . '/' . $template['template_name'] == $selected_templates) ? 'y' : '';
  		  }
  			$r .= $DSP->input_select_option($template['group_name'] . '/' . $template['template_name'], $template['group_name'] . '/' . $template['template_name'], $selected);
  		}

  		$r .= $DSP->input_select_footer();
  	}
  	else
  	{
  		$r .= $DSP->qdiv('highlight_alt', $LANG->line('no_templates'));
  	}

  	return $r;
  }
  
	/**
	 * Display Site Settings
	 */
	function display_site_settings()
	{
		global $DB, $PREFS, $DSP;
		
		$SD = new Fieldframe_SettingsDisplay();

		$r = $SD->block()
		   . $SD->row(array(
		                  $SD->label('null_option_label', 'null_option_desc'),
		                  $SD->text('null_option', $this->site_settings['null_option'])
		              ))
		   . $SD->block_c();

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
		return array(
		  'cell2' => $this->display_cell_settings($field_settings));
  }

  /**
   * Display Cell Settings
   * 
   * @param  array  $cell_settings  The cell's settings
   * @return array  Settings HTML
   */
  function display_cell_settings($cell_settings)
  {
    global $DSP, $LANG;
    
  	// initialize Fieldframe_SettingsDisplay
  	$SD = new Fieldframe_SettingsDisplay();
  	
  	$checked = ($cell_settings['all_templates'] == 'y') ? 1 : 0;
  	
  	$r = '<label>'
         . $DSP->qdiv('itemWrapper defaultBold', $LANG->line('populate_with'))
         . $this->_templates_select($cell_settings['templates'] , 'templates[]', 'y', '')
         . '</label>'
         . '<div style="height: 5px;"></div>'
         . $DSP->input_checkbox('all_templates', 'y', $checked) . $LANG->line('all_templates')
         . '<div style="height: 5px;"></div>';
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
    global $LANG, $DSP, $DB, $FF;
    
    $LANG->fetch_language_file('tbk_template_select');
    
    // initialize Fieldframe_SettingsDisplay
  	$SD = new Fieldframe_SettingsDisplay();
  	
  	$required = isset($FF->row['field_required']) ? $FF->row['field_required'] : null;
		$r = ''; 
		
		if ($field_settings['all_templates'] == 'y') {
		  $r .= $this->_templates_select($field_data, $field_name, '', $required);
		}
		else {
		  if (!empty($field_settings['templates']))
    	{
    		$r .= $DSP->input_select_header($field_name, '', 1, 'auto');
    		$r .= ($required != 'y') ? $DSP->input_select_option('', $this->site_settings['null_option']) : '';

    		foreach($field_settings['templates'] as $template)
    		{
          $selected = ($template == $field_data) ? 'y' : '';
    			$r .= $DSP->input_select_option($template, $template, $selected);
    		}

    		$r .= $DSP->input_select_footer();
    	}
    	else
    	{
    		$r .= $DSP->qdiv('highlight_alt', $LANG->line('no_templates'));
    	}
		}
  	return $r;
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
	 * Display Tag
	 *
	 *
	 * @param  array   $params          Name/value pairs from the opening tag
	 * @param  string  $tagdata         Chunk of tagdata between field tag pairs
	 * @param  string  $field_data      Currently saved field value
	 * @param  array   $field_settings  The field's settings
	 * @return string  
	 */
	function display_tag($params, $tagdata, $field_data, $field_settings)
	{
		global $FF, $TMPL, $DB;
    $r = '';
    if ($params['embed'] == 'y') {
      $r .= '{embed="' . $field_data . '"';
      
      foreach($params as $param => $value) {
        if ($param != 'embed') {
          $r .= ' ' . $param . '="' . $value . '"';
        }
      }
      
      $r .= '}';
    }
    else {
      $r .= $field_data;
    }
    return $r;
  }

}