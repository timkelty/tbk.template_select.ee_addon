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
		'version'  => '1.0',
		'no_lang'  => TRUE
	);

	var $default_field_settings = array(
		'label' => ''
	);

	var $default_cell_settings = array(
		'label' => ''
	);

	/**
	 * Display Field Settings
	 * 
	 * @param  array  $field_settings  The field's settings
	 * @return array  Settings HTML (cell1, cell2, rows)
	 */
	function display_field_settings($field_settings)
	{
		global $DSP;

	}

	/**
	 * Display Field Settings
	 * 
	 * @param  array  $cell_settings  The cell's settings
	 * @return string  Settings HTML
	 */
	function display_cell_settings($cell_settings)
	{
		global $DSP;
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
    global $DSP, $DB, $PREFS, $SESS;

		$sql = "SELECT tg.group_name, t.template_id, t.template_name
					FROM   exp_template_groups tg, exp_templates t
					WHERE  tg.group_id = t.group_id
					AND    tg.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ";
			 
		if (USER_BLOG == TRUE)
		{
			$sql .= "AND tg.group_id = '".$SESS->userdata['tmpl_group_id']."' ";
		}
		else
		{
			$sql .= "AND tg.is_user_blog = 'n' ";
		}
				
		$tquery = $DB->query($sql." ORDER BY tg.group_name, t.template_name");


    $r = $DSP->input_select_header($field_name);
    $r .= $DSP->input_select_option('', 'Choose One...');
    foreach ($tquery->result as $template){
     $tpath = $template['group_name'] .'/' . $template['template_name'];
     $r .= $DSP->input_select_option($tpath, $tpath, (($tpath == $field_data) ? 1 : ''));
    }
     
    $r .= $DSP->input_select_footer();

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