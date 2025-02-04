<?php
/** @var array $categories */

/** @var array $lots */
?>

<section class="lots">
    <div class="lots__header"><?php
        if (!empty($searchQuery)): ?>
            <h2>Результаты поиска по запросу «<span><?= sanitizeInput($searchQuery) ?></span>»</h2>
        <?php
        else: ?>
            <h2>Открытые лоты</h2>
        <?php
        endif; ?>
    </div>

    <?php
    if (empty($lots)): ?>
        <p>Ничего не найдено по вашему запросу</p>
    <?php
    else: ?>
        <ul class="lots__list">
            <!--заполните этот список из массива с товарами-->
            <?php
            foreach ($lots as $lot): ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?= sanitizeInput($lot["image_url"]) ?>" width="350" height="260" alt="<?= $lot["title"] ?>">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= sanitizeInput($lot["category"]) ?></span>
                        <h3 class="lot__title">
                            <a class="text-link" href="lot.php?id=<?= sanitizeInput($lot['id']) ?>">
                                <?= sanitizeInput($lot['title']) ?>
                            </a>
                        </h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">Начальная цена</span>
                                <span class="lot__cost"><?= sanitizeInput(formatPrice($lot["start_price"])) ?></span>
                            </div>
                            <?php
                            $hours = calculatesRemainingTime($lot["ended_at"])[0];
                            $minutes = calculatesRemainingTime($lot["ended_at"])[1];

                            $class = ($hours < 1) ? "timer--finishing" : '';
                            ?>
                            <div class="lot__timer timer <?= $class ?>">
                                <?= $hours ?>:<?= $minutes ?>
                            </div>

                        </div>
                    </div>
                </li>
            <?php
            endforeach; ?>
        </ul>
    <?php
    endif; ?>
</section>
