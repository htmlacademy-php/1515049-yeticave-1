<?php
/** @var array $categories */
/** @var array|null $lot Данные лота */
/** @var $hours */
/** @var $minutes */
/** @var $class */
/** @var $errors */
/** @var $currentPrice */
/** @var $minRate */
/** @var $lotId */
/** @var $userName */
/** @var $isAuctionEnded */
/** @var $isLotOwner */
/** @var $isLastRateByUser */
/** @var $rates */



?>

<section class="lot-item container">
    <h2><?= sanitizeInput($lot['title']); ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?= sanitizeInput($lot['image_url']) ?>" width="730" height="548" alt="Сноуборд">
            </div>
            <p class="lot-item__category"> Категория: <span><?= $lot['category'] ?></span></p>
            <p class="lot-item__description"><?= sanitizeInput($lot['description'])?></p>
        </div>
        <div class="lot-item__right">
            <?php if($userName && !$isAuctionEnded && !$isLotOwner && !$isLastRateByUser):?>
            <div class="lot-item__state">
                <div class="lot-item__timer timer <?= $class ?>">
                    <?=$hours ?>:<?=$minutes ?>
                </div>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount"> Текущая цена </span>
                        <span class="lot-item__cost"><?= sanitizeInput(formatPrice($currentPrice)) ?></span>
                    </div>
                    <div class="lot-item__min-cost">
                        Мин. ставка <span><?= sanitizeInput(formatPrice($minRate)) ?></span>
                    </div>
                </div>
                <form class="lot-item__form" action="lot.php?id=<?= $lotId ?>" method="post" autocomplete="off">
                    <p class="lot-item__form-item form__item <?= isset($errors['cost']) ? 'form__item--invalid' : '' ?>"> <!-- form__item--invalid -->
                        <label for="cost"> Ваша ставка </label>
                        <input id="cost" type="text" name="cost" placeholder="<?= sanitizeInput(formatPrice($minRate)) ?>">
                        <?php if (isset($errors['cost'])): ?>
                            <span class="form__error" style="<?= isset($errors['cost']) ? 'display: block;' : 'display: none;' ?>"><?= $errors['cost'] ?></span>
                        <?php endif; ?>
                    </p>
                    <button type="submit" class="button"> Сделать ставку</button>
                </form>
            </div>
            <?php endif; ?>
            <div class="history">
                <h3> История ставок (<span><?= count($rates) ?></span>
                    <?= getNounPluralForm(count($rates), 'ставка', 'ставки', 'ставок') ?>
                    )</h3>
                <table class="history__list">
                    <?php foreach ($rates as $rate): ?>
                        <tr class="history__item">
                            <td class="history__name"><?= sanitizeInput($rate['name']) ?></td>
                            <td class="history__price"><?= sanitizeInput(formatPrice($rate['amount'])) ?></td>
                            <td class="history__time"><?= timeAgo($rate['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

    </div>
</section>
