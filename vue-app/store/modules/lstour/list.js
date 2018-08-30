import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
    LSTOUR_LIST_ERROR,
    LSTOUR_LIST_LOADING,
    LSTOUR_LIST_RESET,
    LSTOUR_LIST_VIEW,
    LSTOUR_LIST_SUCCESS,
    LSTOUR_LIST_SUCCESS_ITEMS_LIST,
    TOURS_FOR_SHEDULE
} from './mutation-types';


const state = {
    loading: false,
    error: '',
    items: [],
    view: [],
    forShedule: [],
    itemsList: []
};

function error(error) {
    return {type: LSTOUR_LIST_ERROR, error};
}

function loading(loading) {
    return {type: LSTOUR_LIST_LOADING, loading};
}

function success(items) {
    return {type: LSTOUR_LIST_SUCCESS, items};
}

function successItemList(items) {
    return {type: LSTOUR_LIST_SUCCESS_ITEMS_LIST, items};
}

function setForShedule(items) {
    return {type: TOURS_FOR_SHEDULE, items};
}


function view(items) {
    return {type: LSTOUR_LIST_VIEW, items};
}

const getters = {
    error: state => state.error,
    items: state => state.items,
    loading: state => state.loading,
    view: state => state.view,
    forShedule: state => state.forShedule,
    itemsList: state => state.itemsList
};

const actions = {
    getItems({commit}, page = '/l_s_tours') {
        commit(loading(true));

        fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(success(data['hydra:member']));
                commit(view(data['hydra:view']));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.e.message));
            });
    },
    getItemsList({commit}, search = null, page = '/list-ls-tour') {
        commit(loading(true));

        return fetch(page,{
            method: 'PUT',
            credentials: "same-origin",
            headers: new Headers({'Content-Type': 'application/ld+json'}),
            body: JSON.stringify(search),
        })
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(successItemList(data));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    },
    getForShedule({commit}, page = '/tours-for-shedule') {
        commit(loading(true));
        return fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(setForShedule(data));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    },
    reset({commit}) {
        commit(loading(false));
    }
};

const mutations = {
    [LSTOUR_LIST_ERROR](state, payload) {
        state.error = payload.error;
    },
    [LSTOUR_LIST_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [LSTOUR_LIST_VIEW](state, payload) {
        state.view = payload.items;
    },
    [LSTOUR_LIST_SUCCESS](state, payload) {
        state.items = payload.items;
    },
    [LSTOUR_LIST_SUCCESS_ITEMS_LIST](state, payload) {
        state.itemsList = payload.items;
    },
    [TOURS_FOR_SHEDULE](state, payload) {
        state.forShedule = payload.items;
    },
    [LSTOUR_LIST_RESET](state) {
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
