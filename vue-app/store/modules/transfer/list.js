import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  TRANSFER_LIST_ERROR,
  TRANSFER_LIST_LOADING,
  TRANSFER_LIST_RESET,
  TRANSFER_LIST_VIEW,
  TRANSFER_LIST_SUCCESS
} from './mutation-types';
import SubmissionError from "../../../error/SubmissionError";

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: TRANSFER_LIST_ERROR, error};
}

function loading(loading) {
  return {type: TRANSFER_LIST_LOADING, loading};
}

function success(items) {
  return {type: TRANSFER_LIST_SUCCESS, items};
}

function view(items) {
  return { type: TRANSFER_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, page = '/transfers') {
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
          commit(error(e.message));
        });
    },

    getItemsList({ commit }, search = null, page = '/transfer-list') {
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
                commit(success(data));

            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' +e.message));
            });
    },
    sendMails({ commit }, values) {
        commit(loading(true));

        return fetch('/transfer-send-mails', {
            method: 'POST',
            credentials: "same-origin",
            body: JSON.stringify(values)})
            .then(response => {
                commit(loading(false));

                return response.json();
            })
            .catch(e => {
                commit(loading(false));

                if (e instanceof SubmissionError) {
                    commit(violations(e.errors));
                    commit(error(time() + ' ' +e.errors._error));
                    return;
                }

                commit(error(time() + ' ' +e.message));
            });
    }
};

const mutations = {
    [TRANSFER_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [TRANSFER_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [TRANSFER_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [TRANSFER_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [TRANSFER_LIST_RESET] (state) {
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
