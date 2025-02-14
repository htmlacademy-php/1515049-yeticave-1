<?php

/**
 * @var string $winnerName Имя победителя
 * @var string $lotTitle Название лота
 * @var string $lotLink Ссылка на лот
 * @var string $ratesLink Ссылка на ставки
 */
?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= sanitizeInput($winnerName) ?></p>
<p>Ваша ставка для лота <a href="<?= sanitizeInput($lotLink) ?>"><?= sanitizeInput($lotTitle) ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?= sanitizeInput($ratesLink) ?>">мои ставки</a>, чтобы связаться с автором объявления.</p>
<small>Интернет-аукцион "YetiCave"</small>
