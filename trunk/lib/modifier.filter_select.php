<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty filter_select modifier plugin
 *
 * Type:     modifier<br>
 * Name:     filter_select<br>
 * Purpose:  filter atom entries by DASe metadata and return a metadata value 
 * @author   Peter Keane <daseproject.org>
 * @param    Dase_Atom_Entry  
 * @param    string 
 * @param    string 
 * @param    string 
 * @return   string 
 */
function smarty_modifier_filter_select(Dase_Atom_Feed $feed, $attribute_ascii_id, $value_text, $return_attribute_ascii_id)
{
    foreach ($feed->entries as $entry) {
        foreach ($entry->metadata as $att_ascii => $keyval) {
            if ($attribute_ascii_id == $att_ascii) {
                if (in_array($value_text,$keyval['values'])) {
                    foreach ($entry->metadata as $k => $v) {
                        if ($return_attribute_ascii_id == $k) {
                            return $v['values'][0];
                        }
                    }
                }
            }
        } 
    }
}
