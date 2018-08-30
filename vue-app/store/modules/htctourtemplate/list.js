import fetch from '../../../utils/fetch';
import {
    HTCTOURTEMPLATE_LIST_ERROR,
    HTCTOURTEMPLATE_LIST_LOADING,
    HTCTOURTEMPLATE_LIST_RESET,
    HTCTOURTEMPLATE_LIST_VIEW,
    HTCTOURTEMPLATE_LIST_SUCCESS,
    NOTIFICATION_LIST_SUCCESS
} from './mutation-types';
import time from "../../../utils/time";
import SubmissionError from "../../../error/SubmissionError";

const state = {
    loading: false,
    error: '',
    items: [],
    view: [],
    code: [],
    notifications: [],
};

function error(error) {
    return {type: HTCTOURTEMPLATE_LIST_ERROR, error};
}

function loading(loading) {
    return {type: HTCTOURTEMPLATE_LIST_LOADING, loading};
}

function success(items) {
    return {type: HTCTOURTEMPLATE_LIST_SUCCESS, items};
}

function successNotification(items) {
    return {type: NOTIFICATION_LIST_SUCCESS, items};
}

function view(items) {
    return {type: HTCTOURTEMPLATE_LIST_VIEW, items};
}

function setCode(items) {
    return {type: 'setCode', items};
}

const getters = {
    error: state => state.error,
    items: state => state.items,
    loading: state => state.loading,
    view: state => state.view,
    code: state => state.code,
    notifications: state => state.notifications
};

const actions = {
    getItems({commit}, pagination = false) {
        commit(loading(true));
        let page = '/h_t_c_tour_templates?pagination=' + pagination;
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

    getHTCNotification({commit}) {
        commit(loading(true));
        let page = '/notification-type/htc';
        return fetch(page)
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

    getCode({commit}, page = '/code_tour_one_days') {
        commit(loading(true));
        fetch(page)
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
    updateCode({commit, state}, {item, values}) {
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
    [HTCTOURTEMPLATE_LIST_ERROR](state, payload) {
        state.error = payload.error;
    },
    [HTCTOURTEMPLATE_LIST_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [HTCTOURTEMPLATE_LIST_VIEW](state, payload) {
        state.view = payload.items;
    },
    [HTCTOURTEMPLATE_LIST_SUCCESS](state, payload) {
        state.items = payload.items;
    },
    [NOTIFICATION_LIST_SUCCESS](state, payload) {
        state.notifications = payload.items;
    },
    [HTCTOURTEMPLATE_LIST_RESET](state) {
        state.items = [];
    },
    setCode(state, payload) {
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
