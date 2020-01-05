<?php

declare(strict_types=1);

namespace Test\Article;

use Ramsey\Uuid\UuidInterface;

final class ArticleFactory
{
	public function create(ArticleFactory $data): ArticleFactory
	{
		return new Article(Uuid::uuid4(), $data);
	}
}