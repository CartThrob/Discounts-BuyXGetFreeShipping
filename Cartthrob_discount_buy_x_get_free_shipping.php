<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_discount_buy_x_get_free_shipping extends Cartthrob_discount
{
	public $title = 'buy_x_get_free_shipping';

	public $settings = array(
 		array(
			'name' => 'purchase_quantity',
			'short_name' => 'buy_x',
			'note' => 'enter_the_purchase_quantity',
			'type' => 'text'
		),
		array(
			'name' => 'qualifying_entry_ids',
			'short_name' => 'entry_ids',
			'note' => 'separate_multiple_entry_ids',
			'type' => 'text'
		),
	);


	function get_discount()
	{
		$discount 			= 0;
		$entry_ids 			= array();
		$not_entry_ids 		= array();
		
		// CHECK ENTRY IDS
		if ( $this->plugin_settings('entry_ids') )
		{
			if (preg_match('/^not (.*)/',  trim( $this->plugin_settings('entry_ids') ) , $matches))
			{
				$not_entry_ids = preg_split('/\s*,\s*/',  $matches[1]);
			}
			else
			{
				$entry_ids = preg_split('/\s*,\s*/', trim( $this->plugin_settings('entry_ids') ));
			}
		}
 		foreach ($this->core->cart->items() as $item)
		{
			if (count($entry_ids) > 0)
			{
				if ( $item->product_id() && in_array( $item->product_id(), $entry_ids))
				{
					if ($item->quantity() >= $this->plugin_settings('buy_x'))
					{
 						return $this->core->cart->shipping();
					}
				}
			}

		}
	}

	function validate()
	{
		
		$entry_ids = array();
		$not_entry_ids = array();

		if (  $this->plugin_settings('entry_ids') )
		{
			$entry_ids = preg_split('/\s*,\s*/', trim($this->plugin_settings('entry_ids')));

			if (preg_match('/^not (.*)/',  trim( $this->plugin_settings('entry_ids') ) , $matches))
			{
				$codes = (explode('not', $matches[1], 2));
				$not_entry_ids = preg_split('/\s*,\s*/',  $codes[1]);
			}
		}
		
 		if (count($entry_ids))
		{
				foreach ($this->core->cart->items() as $item)
				{
					if ( $item->product_id()  && in_array( $item->product_id(), $entry_ids))
					{
						
						if ( $item->quantity() >= $this->core->sanitize_number( $this->plugin_settings('buy_x') ))
						{
 							return TRUE;
						}
					}
				$this->set_error( $this->core->lang('coupon_minimum_not_reached') );
				}
			}	
		elseif(count($not_entry_ids))
		{
			foreach ($this->core->cart->items() as $item)
			{
					if ( $item->product_id()  && ! in_array( $item->product_id(), $entry_ids))
					{
						if ( $item->quantity() > $this->core->sanitize_number( $this->plugin_settings('buy_x') ))
						{
							return TRUE;
						}
					}
				$this->set_error( $this->core->lang('coupon_minimum_not_reached'));
			}	
		}
		else
		{
			$this->set_error( $this->core->lang('coupon_not_valid_for_items') );
		}
		return FALSE;
	}
}