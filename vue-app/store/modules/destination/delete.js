import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
    DESTINATION_DELETE_ERROR,
    DESTINATION_DELETE_LOADING,
    DESTINATION_DELETE_SUCCESS,
    DESTINATION_DELETE_RESET
} from './mutation-types';
import SubmissionError from "../../../error/SubmissionError";

const state = {
    loading: false,
    error: '',
    deleted: null
};

function error(error) {
    return {type: DESTINATION_DELETE_ERROR, error};
}

function loading(loading) {
    return {type: DESTINATION_DELETE_LOADING, loading};
}

function success(deleted) {
    return {type: DESTINATION_DELETE_SUCCESS, deleted};
}

function reset() {
    return {type: DESTINATION_DELETE_RESET};
}

const getters = {
    error: state => state.error,
    deleted: state => state.deleted,
    loading: state => state.loading,
};

const actions = {
    delete({commit}, item) {
        commit(loading(true));
        return fetch(item['@id'], {method: 'DELETE'})
            .then(() => {
                commit(loading(false));
                commit(success(item));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    },
    deleteItems({commit}, param) {
        commit(loading(true));
        let page = '/delete-various/' + param.entity;
        return fetch(page, {method: 'POST', body: JSON.stringify({items: param.items})})
            .then(response => {
                commit(loading(false));
                return response.json();
            })
            .then(data => {
                commit(success(data));
            })
            .catch(e => {
                commit(loading(false));

                if (e instanceof SubmissionError) {
                    commit(violations(e.errors));
                    commit(error(e.errors._error));
                    return;
                }
                commit(error(e.message));
            });
    },
    reset({commit}) {
        commit(reset());
    }
};

const mutations = {
    [DESTINATION_DELETE_ERROR](state, payload) {
        state.error = payload.error;
    },
    [DESTINATION_DELETE_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [DESTINATION_DELETE_SUCCESS](state, payload) {
        state.deleted = payload.deleted;
    },
    [DESTINATION_DELETE_RESET](state) {
        state.error = '';
        state.loading = false;
        state.deleted = null;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
