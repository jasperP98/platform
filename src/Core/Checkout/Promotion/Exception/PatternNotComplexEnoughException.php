<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Promotion\Exception;

use Shopware\Core\Checkout\Promotion\PromotionException;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class PatternNotComplexEnoughException extends PromotionException
{
}
