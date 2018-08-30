import SubmissionError from '../../../error/SubmissionError';
import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
    GUIDE_UPDATE_RESET,
    GUIDE_UPDATE_UPDATE_ERROR,
    GUIDE_UPDATE_UPDATE_LOADING,
    GUIDE_UPDATE_UPDATE_SUCCESS,
    GUIDE_UPDATE_RETRIEVE_ERROR,
    GUIDE_UPDATE_RETRIEVE_LOADING,
    GUIDE_UPDATE_RETRIEVE_SUCCESS,
    GUIDE_UPDATE_UPDATE_VIOLATIONS
} from './mutation-types';


const state = {
    loading: false,
    retrieveError: '',
    retrieveLoading: false,
    retrieved: null,
    updated: null,
    updateError: '',
    updateLoading: false,
    violations: null
};

function retrieveError(retrieveError) {
    return {type: GUIDE_UPDATE_RETRIEVE_ERROR, retrieveError};
}

function retrieveLoading(retrieveLoading) {
    return {type: GUIDE_UPDATE_RETRIEVE_LOADING, retrieveLoading};
}

function retrieveSuccess(retrieved) {
    return {type: GUIDE_UPDATE_RETRIEVE_SUCCESS, retrieved};
}

function updateError(updateError) {
    return {type: GUIDE_UPDATE_UPDATE_ERROR, updateError};
}

function updateLoading(updateLoading) {
    return {type: GUIDE_UPDATE_UPDATE_LOADING, updateLoading};
}

function updateSuccess(updated) {
    return {type: GUIDE_UPDATE_UPDATE_SUCCESS, updated};
}

function violations(violations) {
    return {type: GUIDE_UPDATE_UPDATE_VIOLATIONS, violations};
}

function reset() {
    return {type: GUIDE_UPDATE_RESET};
}

const getters = {
    loading: state => state.loading,
    retrieveError: state => state.retrieveError,
    retrieveLoading: state => state.retrieveLoading,
    retrieved: state => state.retrieved,
    updated: state => state.updated,
    updateError: state => state.updateError,
    updateLoading: state => state.updateLoading,
    violations: state => state.violations
};

const actions = {
    retrieve({commit}, id) {
        commit(retrieveLoading(true));

        return fetch(id)
            .then(response => response.json())
            .then(data => {
                commit(retrieveLoading(false));
                commit(retrieveSuccess(data));
            })
            .catch(e => {
                commit(retrieveLoading(false));
                commit(retrieveError(e.message));
            });
    },
    update({commit, state}, {item, values}) {

        commit(updateLoading(true));

        return fetch(item['@id'], {
                method: 'PUT',
                headers: new Headers({'Content-Type': 'application/ld+json'}),
                body: JSON.stringify(values),
            }
        )
            .then(response => response.json())
            .then(data => {
                commit(updateLoading(false));
                commit(updateSuccess(data));
            })
            .catch(e => {
                commit(updateLoading(false));
                if (e instanceof SubmissionError) {
                    commit(violations(e.errors));
                    commit(updateError(time() + ' ' + e.errors._error));
                    return;
                }
                commit(updateError(time() + ' ' + e.message));
            });
    },
    reset({commit}) {
        commit(reset());
    }
};

const mutations = {
    [GUIDE_UPDATE_RETRIEVE_ERROR](state, payload) {
        state.retrieveError = payload.retrieveError;
    },
    [GUIDE_UPDATE_RETRIEVE_LOADING](state, payload) {
        state.retrieveLoading = payload.retrieveLoading;
    },
    [GUIDE_UPDATE_RETRIEVE_SUCCESS](state, payload) {
        state.retrieved = payload.retrieved;
    },
    [GUIDE_UPDATE_UPDATE_LOADING](state, payload) {
        state.updateLoading = payload.updateLoading;
    },
    [GUIDE_UPDATE_UPDATE_ERROR](state, payload) {
        state.updateError = payload.updateError;
    },
    [GUIDE_UPDATE_UPDATE_SUCCESS](state, payload) {
        state.updated = payload.updated;
        state.violations = null;
    },
    [GUIDE_UPDATE_UPDATE_VIOLATIONS](state, payload) {
        state.violations = payload.violations;
    },
    [GUIDE_UPDATE_RESET](state) {
        state.loading = false;
        state.retrieveError = '';
        state.retrieveLoading = false;
        state.retrieved = null;
        state.updated = null;
        state.updateError = '';
        state.updateLoading = false;
        state.violations = null;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
