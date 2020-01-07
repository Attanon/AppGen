<?php

declare(strict_types=1);

namespace Test\Article;

final class ArticleDataFactory
{
	public function createFromFormData(array $formData): ArticleData
	{
		$data = new ArticleData();
		$data->slug = $formData['slug'];
		$data->title = $formData['title'];
		$data->content = $formData['content'];

		return $data;
	}
}
