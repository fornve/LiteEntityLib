<?php

interface dbdriver
{
	protected $resource;
	protected $result;

	public function connect()
	{
	}

	public function query()
	{
	}

	public function fetch()
	{
	}

	public function fetchAll()
	{
	}

	public function escape()
	{
	}

	public function disconnect()
	{
	}
}
