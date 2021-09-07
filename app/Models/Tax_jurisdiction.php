<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

/**
 * Tax Jurisdiction class
 */

class Tax_jurisdiction extends Model
{
	/**
	 *  Determines if it exists in the table
	 */
	public function exists($jurisdiction_id)
	{
		$builder = $this->db->table('tax_jurisdictions');
		$builder->where('jurisdiction_id', $jurisdiction_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/**
	 *  Gets total of rows
	 */
	public function get_total_rows()
	{
		$builder = $this->db->table('tax_jurisdictions');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/**
	 * Gets information about the particular record
	 */
	public function get_info($jurisdiction_id)
	{
		$builder = $this->db->table('tax_jurisdictions');
		$builder->where('jurisdiction_id', $jurisdiction_id);
		$builder->where('deleted', 0);
		$query = $builder->get();

		if($query->getNumRows()==1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object
			$tax_jurisdiction_obj = new stdClass();

			//Get all the fields from the table
			foreach($this->db->getFieldNames('tax_jurisdictions') as $field)
			{
				$tax_jurisdiction_obj->$field = '';
			}
			return $tax_jurisdiction_obj;
		}
	}

	/**
	 *  Returns all rows from the table
	 */
	public function get_all($rows = 0, $limit_from = 0, $no_deleted = TRUE)
	{
		$builder = $this->db->table('tax_jurisdictions');
		if($no_deleted == TRUE)
		{
			$builder->where('deleted', 0);
		}

		$builder->orderBy('jurisdiction_name', 'asc');

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/**
	 *  Returns multiple rows
	 */
	public function get_multiple_info($jurisdiction_ids)
	{
		$builder = $this->db->table('tax_jurisdictions');
		$builder->whereIn('jurisdiction_id', $jurisdiction_ids);
		$builder->orderBy('jurisdiction_name', 'asc');

		return $builder->get();
	}

	/**
	 *  Inserts or updates a row
	 */
	public function save(&$jurisdiction_data, $jurisdiction_id = FALSE)
	{
		if(!$jurisdiction_id || !$this->exists($jurisdiction_id))
		{
			if($builder->insert('tax_jurisdictions', $jurisdiction_data))
			{
				$jurisdiction_data['jurisdiction_id'] = $this->db->insertID();
				return TRUE;
			}

			return FALSE;
		}

		$builder->where('jurisdiction_id', $jurisdiction_id);

		return $builder->update('tax_jurisdictions', $jurisdiction_data);
	}

	/**
	 * Saves changes to the tax jurisdictions table
	 */
	public function save_jurisdictions($array_save)
	{
		$this->db->transStart();

		$not_to_delete = array();

		foreach($array_save as $key => $value)
		{
			// save or update
			$tax_jurisdiction_data = array('jurisdiction_name' => $value['jurisdiction_name'], 'tax_group' => $value['tax_group'], 'tax_type' => $value['tax_type'], 'reporting_authority' => $value['reporting_authority'], 'tax_group_sequence' => $value['tax_group_sequence'], 'cascade_sequence' => $value['cascade_sequence'], 'deleted' => '0');
			$this->save($tax_jurisdiction_data, $value['jurisdiction_id']);
			if($value['jurisdiction_id'] == -1)
			{
				$not_to_delete[] = $tax_jurisdiction_data['jurisdiction_id'];
			}
			else
			{
				$not_to_delete[] = $value['jurisdiction_id'];
			}
		}

		// all entries not available in post will be deleted now
		$deleted_tax_jurisdictions = $this->get_all()->getResultArray();

		foreach($deleted_tax_jurisdictions as $key => $tax_jurisdiction_data)
		{
			if(!in_array($tax_jurisdiction_data['jurisdiction_id'], $not_to_delete))
			{
				$this->delete($tax_jurisdiction_data['jurisdiction_id']);
			}
		}

		$this->db->transComplete();
		return $this->db->transStatus();
	}

	/**
	 * Soft deletes a specific tax jurisdiction
	 */
	public function delete($jurisdiction_id)
	{
		$builder->where('jurisdiction_id', $jurisdiction_id);

		return $builder->update('tax_jurisdictions', array('deleted' => 1));
	}

	/**
	 * Soft deletes a list of rows
	 */
	public function delete_list($jurisdiction_ids): bool
	{
		$builder->whereIn('jurisdiction_id', $jurisdiction_ids);

		return $builder->update('tax_jurisdictions', array('deleted' => 1));
 	}

	/**
	 * Gets rows
	 */
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'jurisdiction_name', 'asc', TRUE);
	}

	/**
	 *  Perform a search for a set of rows
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'jurisdiction_name', $order='asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(tax_jurisdictions.jurisdiction_id) as count');
		}

		$builder = $this->db->table('tax_jurisdictions AS tax_jurisdictions');
		$builder->groupStart();
			$builder->like('jurisdiction_name', $search);
			$builder->orLike('reporting_authority', $search);
		$builder->groupEnd();
		$builder->where('deleted', 0);

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	public function get_empty_row()
	{
		return array('0' => array(
			'jurisdiction_id' => -1,
			'jurisdiction_name' => '',
			'tax_group' => '',
			'tax_type' => '1',
			'reporting_authority' => '',
			'tax_group_sequence' => '',
			'cascade_sequence' => '',
			'deleted' => ''));
	}

}
?>