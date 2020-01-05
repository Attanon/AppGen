<?php

declare(strict_types=1);

namespace Test\Article\Event;

use Test\Article\Article;

class ArticleDeletedEvent
{
	public Article $article;

	public function __construct(Article $article)
	{
		$this->article = $article;
	}

	public function getArticle(): Article
	{
		return $this->article;
	}
}
