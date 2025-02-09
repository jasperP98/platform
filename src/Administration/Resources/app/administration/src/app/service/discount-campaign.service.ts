import type { DiscountCampaign } from 'src/module/sw-extension/service/extension-store-action.service';

/**
 * @private
 * @sw-package checkout
 */
export default class ShopwareDiscountCampaignService {
    public isDiscountCampaignActive(discountCampaign: DiscountCampaign) {
        if (!discountCampaign || !discountCampaign.startDate) {
            return false;
        }

        const now = new Date();

        if (new Date(discountCampaign.startDate) > now) {
            return false;
        }

        if (typeof discountCampaign.endDate === 'string' && new Date(discountCampaign.endDate) < now) {
            return false;
        }

        if (
            typeof discountCampaign.discountAppliesForMonths === 'number' &&
            discountCampaign.discountAppliesForMonths === 0
        ) {
            return false;
        }

        // discounts without end date are always valid
        return true;
    }

    public isSamePeriod(discountCampaign: DiscountCampaign, comparator: DiscountCampaign) {
        const discountDuration = discountCampaign.discountAppliesForMonths || null;
        const comparatorDuration = comparator.discountAppliesForMonths || null;

        return (
            discountCampaign.startDate === comparator.startDate &&
            discountCampaign.endDate === comparator.endDate &&
            discountDuration === comparatorDuration
        );
    }
}

/**
 * @private
 * @sw-package checkout
 */
export type { ShopwareDiscountCampaignService };
