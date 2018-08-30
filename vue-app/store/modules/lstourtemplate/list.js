import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
    LSTOURTEMPLATE_LIST_ERROR,
    LSTOURTEMPLATE_LIST_LOADING,
    LSTOURTEMPLATE_LIST_RESET,
    LSTOURTEMPLATE_LIST_VIEW,
    LSTOURTEMPLATE_LIST_SUCCESS,
    NOTIFICATION_LIST_SUCCESS
} from './mutation-types';
import SubmissionError from "../../../error/SubmissionError";

const state = {
    loading: false,
    error: '',
    items: [],
    view: [],
    notifications: [],
    code: []
};

function error(error) {
    return {type: LSTOURTEMPLATE_LIST_ERROR, error};
}

function loading(loading) {
    return {type: LSTOURTEMPLATE_LIST_LOADING, loading};
}

function success(items) {
    return {type: LSTOURTEMPLATE_LIST_SUCCESS, items};
}

function successNotification(items) {
    return {type: NOTIFICATION_LIST_SUCCESS, items};
}

function view(items) {
    return {type: LSTOURTEMPLATE_LIST_VIEW, items};
}
function setCode(items) {
    return { type: 'setCode', items};
}

const getters = {
    error: state => state.error,
    items: state => state.items,
    loading: state => state.loading,
    view: state => state.view,
    notifications: state => state.notifications,
    code: state => state.code
};

const actions = {
    getItems({commit}, pagination = false) {
        commit(loading(true));
        let page = '/l_s_tour_templates?pagination=' + pagination;
        fetch(page)
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
    },
    getLsNotification({commit}) {
        commit(loading(true));
        let page = '/notification-type/ls';
        return fetch(page, {method: 'GET'})
            .then(response => {
                commit(loading(false));
                return response.json();
            })
            .then(data => {
                commit(successNotification(data));
            })
            .catch(e => {
                commit(loading(false));

                if (e instanceof SubmissionError) {
                    commit(violations(e.errors));
                    commit(error(time() + ' ' + e.errors._error));
                    return;
                }
                commit(error(time() + ' ' + e.message));
            });
    },
    getCode({ commit }, page = '/code_tours') {
        commit(loading(true));
        return fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(setCode(data['hydra:member']));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    },
    updateCode({ commit, state }, { item, values }) {
        commit(loading(true));
        return fetch(item['@id'], {
                method: 'PUT',
                headers: new Headers({'Content-Type': 'application/ld+json'}),
                body: JSON.stringify(values),
            }
        )
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
            })
            .catch(e => {
                commit(loading(false));

                if (e instanceof SubmissionError) {
                    commit(error(time() + ' ' + e.errors._error));
                    return;
                }

                commit(error(time() + ' ' + e.message));
            });
    },
};

const mutations = {
    [LSTOURTEMPLATE_LIST_ERROR](state, payload) {
        state.error = payload.error;
    },
    [LSTOURTEMPLATE_LIST_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [LSTOURTEMPLATE_LIST_VIEW](state, payload) {
        state.view = payload.items;
    },
    [LSTOURTEMPLATE_LIST_SUCCESS](state, payload) {
        state.items = payload.items;
    },
    [NOTIFICATION_LIST_SUCCESS](state, payload) {
        state.notifications = payload.items;
    },
    [LSTOURTEMPLATE_LIST_RESET](state) {
        state.items = [];
    },
    setCode(state, payload){
        state.code = payload.items
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
