import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
    ICON_DELETE_ERROR,
    ICON_DELETE_LOADING,
    ICON_DELETE_SUCCESS,
    ICON_DELETE_RESET
} from './mutation-types';
import SubmissionError from "../../../error/SubmissionError";

const state = {
    loading: false,
    error: '',
    deleted: null
};

function error(error) {
    return {type: ICON_DELETE_ERROR, error};
}

function loading(loading) {
    return {type: ICON_DELETE_LOADING, loading};
}

function success(deleted) {
    return {type: ICON_DELETE_SUCCESS, deleted};
}

function reset() {
    return {type: ICON_DELETE_RESET};
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
    reset({commit}) {
        commit(reset());
    }
};

const mutations = {
    [ICON_DELETE_ERROR](state, payload) {
        state.error = payload.error;
    },
    [ICON_DELETE_LOADING](state, payload) {
        state.loading = payload.loading;
    },
    [ICON_DELETE_SUCCESS](state, payload) {
        state.deleted = payload.deleted;
    },
    [ICON_DELETE_RESET](state) {
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
