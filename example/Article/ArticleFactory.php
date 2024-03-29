<?php

declare(strict_types=1);

namespace Test\Article;

use Ramsey\Uuid\Uuid;

final class ArticleFactory
{
	public function create(ArticleData $data): Article
	{
		return new Article(Uuid::uuid4(), $data);
	}
}
