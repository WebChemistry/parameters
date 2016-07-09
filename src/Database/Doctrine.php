<?php

namespace WebChemistry\Parameters\Database;

use Doctrine\ORM\EntityManager;
use Nette\Object;
use WebChemistry\Parameters\IDatabase;
use WebChemistry\Parameters\IEntity;

class Doctrine extends Object implements IDatabase {

	/** @var \Doctrine\ORM\EntityManager */
	private $em;

	/** @var string */
	private $entity;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @return array
	 */
	public function getPairs() {
		$return = [];
		foreach ($this->findAll() as $row) {
			$return[$row->getId()] = $row->getContent();
		}

		return $return;
	}

	/**
	 * @param string $id
	 * @param mixed $value
	 * @return void
	 */
	public function persist($id, $value) {
		$entity = $this->createEntity();
		$entity->setId($id);
		$entity->setContent(is_array($value) ? serialize($value) : $value);
		$entity->setIsSerialized(is_array($value));

		$this->em->persist($entity);
	}

	/**
	 * @param string $id
	 * @param string|int|float $value
	 * @return void
	 */
	public function merge($id, $value) {
		$entity = $this->createEntity();
		$entity->setId($id);
		$entity->setContent(is_array($value) ? serialize($value) : $value);
		$entity->setIsSerialized(is_array($value));

		$this->em->merge($entity);
	}

	/**
	 * @return void
	 */
	public function flush() {
		$this->em->flush();
	}

	/**
	 * @return void
	 */
	public function clean() {
		$builder = $this->getRepository()->createQueryBuilder('e')->delete();
		$builder->getQuery()->getResult();
	}

	/**
	 * @return IEntity
	 */
	private function createEntity() {
		return new $this->entity;
	}

	/**
	 * @param string $entity
	 * @return self
	 */
	public function setEntity($entity) {
		$this->entity = $entity;

		return $this;
	}

	/**
	 * @return IEntity[]
	 */
	private function findAll() {
		return $this->getRepository()->findAll();
	}

	private function getRepository() {
		return $this->em->getRepository($this->entity);
	}

}
