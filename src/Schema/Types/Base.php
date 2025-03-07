<?php
namespace SlimSEO\Schema\Types;

abstract class Base {
	protected $url;
	protected $context;
	protected $id;
	protected $properties = [];
	protected $references = [];

	public function __construct( $url = null, $context = null ) {
		$this->url     = $url ?: $this->get_current_url();
		$this->context = $context ?: $this->get_type();
		$this->id      = $this->url . '#' . $this->context;
	}

	public function __set( $name, $value ) {
		$this->$name = $value;
	}

	public function __get( $name ) {
		return $this->$name;
	}

	public function is_active() {
		return apply_filters( "slim_seo_schema_{$this->context}_enable", true );
	}

	public function get_current_url() {
		global $wp;

		$url = add_query_arg( [], $wp->request );
		$url = home_url( $url );
		$url = strtok( $url, '#' );
		$url = strtok( $url, '?' );

		return $url;
	}

	public function get_type() {
		$class = get_class( $this );
		$pos   = strrpos( $class, '\\' );
		$type  = substr( $class, $pos + 1 );

		return strtolower( $type );
	}

	public function add_reference( $name, $entity ) {
		if ( $entity->is_active() ) {
			$this->references[ $name ] = $entity;
		}
	}

	public function add_property( $name, $value ) {
		$this->properties[ $name ] = $value;
	}

	public function get_schema() {
		$schema = $this->generate();
		$schema = array_merge( $schema, $this->properties );

		foreach ( $this->references as $name => $entity ) {
			$schema[ $name ] = [ '@id' => $entity->id ];
		}

		$schema = array_filter( $schema );
		$schema = apply_filters( "slim_seo_schema_{$this->context}", $schema );

		return $schema;
	}

	abstract function generate();
}
