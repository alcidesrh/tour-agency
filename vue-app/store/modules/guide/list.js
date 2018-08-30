import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
    GUIDE_LIST_ERROR,
    GUIDE_LIST_LOADING,
    GUIDE_LIST_RESET,
    GUIDE_LIST_VIEW,
    GUIDE_LIST_SUCCESS
} from './mutation-types';

const state = {
    loading: false,
    error: '',
    items: [],
    view: []
};

function error(error) {
    return {type: GUIDE_LIST_ERROR, error};
}

function loading(loading) {
    return {type: GUIDE_LIST_LOADING, loading};
}

function success(items) {
    return {type: GUIDE_LIST_SUCCESS, items};
}

function view(items) {
    return {type: GUIDE_LIST_VIEW, items};
}

const getters = {
    error: state => state.error,
    items: state => state.items,
    loading: state => state.loading,
    view: state => state.view
};

const actions = {
    getItems({commit}, pagination = false) {
        commit(loading(true));
        let page = '/guides?pagination=' + pagination;
        return fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(success(data['hydra:member']));
                commit(view(data['hydra:view']));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    }
};

const mutations = {
    [GUIDE_LIST_ERROR](state, payload) {
        state.error = payload.error;
    },
    [GUIDE_LIST_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [GUIDE_LIST_VIEW](state, payload) {
        state.view = payload.items;
    },
    [GUIDE_LIST_SUCCESS](state, payload) {
        state.items = payload.items;
    },
    [GUIDE_LIST_RESET](state) {
        state.items = [];
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
