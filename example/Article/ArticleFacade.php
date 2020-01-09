<?php

declare(strict_types=1);

namespace Test\Article;

use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Test\Article\Event\ArticleCreatedEvent;
use Test\Article\Event\ArticleDeletedEvent;
use Test\Article\Event\ArticleUpdatedEvent;

final class ArticleFacade extends ArticleRepository
{
	private ArticleFactory $articleFactory;
	private EntityManagerInterface $entityManager;
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(ArticleFactory $articleFactory, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
	{
		parent::__construct($entityManager);
		$this->articleFactory = $articleFactory;
		$this->entityManager = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function create(ArticleData $data): Article
	{
		$article = $this->articleFactory->create($data);

		$this->entityManager->persist($article);
		$this->entityManager->flush();

		$this->eventDispatcher->dispatch(new ArticleCreatedEvent($article));

		return $article;
	}

	public function edit(UuidInterface $id, ArticleData $data): Article
	{
		$article = $this->get($id);

		$article->edit($data);
		$this->entityManager->flush();

		$this->eventDispatcher->dispatch(new ArticleUpdatedEvent($article));

		return $article;
	}

	public function delete(UuidInterface $id): void
	{
		$article = $this->get($id);

		$this->eventDispatcher->dispatch(new ArticleDeletedEvent($article));

		$this->entityManager->remove($article);
		$this->entityManager->flush();
	}
}
