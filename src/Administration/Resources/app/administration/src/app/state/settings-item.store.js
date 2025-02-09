const { hasOwnProperty } = Shopware.Utils.object;

/**
 * @sw-package framework
 * @private
 * @deprecated tag:v6.7.0 - Will be replaced with Pinia store
 */
export default {
    namespaced: true,
    state: {
        settingsGroups: {
            shop: [],
            system: [],
            plugins: [],
        },
    },

    mutations: {
        addItem(state, settingsItem) {
            const group = settingsItem.group;

            if (!hasOwnProperty(state.settingsGroups, group)) {
                state.settingsGroups[group] = [];
            }

            if (state.settingsGroups[group].some((setting) => setting.name === settingsItem.name)) {
                return;
            }

            state.settingsGroups[group].push(settingsItem);
        },
    },
};
