<?php use function App\{e, url}; /** @var array $v */ ?>
<figure class="clip">
  <div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($v['youtube']) ?>" title="<?= e($v['title']) ?>" loading="lazy" allowfullscreen></iframe></div>
  <figcaption><strong><?= e($v['title']) ?></strong> · <a href="<?= e(url($v['artist']['slug'])) ?>"><?= e($v['artist']['name']) ?></a></figcaption>
</figure>
