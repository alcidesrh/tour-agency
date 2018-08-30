import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
  NOTIFICATION_LIST_ERROR,
  NOTIFICATION_LIST_LOADING,
  NOTIFICATION_LIST_RESET,
  NOTIFICATION_LIST_VIEW,
  NOTIFICATION_LIST_SUCCESS
} from './mutation-types';
import SubmissionError from "../../../error/SubmissionError";

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: NOTIFICATION_LIST_ERROR, error};
}

function loading(loading) {
  return {type: NOTIFICATION_LIST_LOADING, loading};
}

function success(items) {
  return {type: NOTIFICATION_LIST_SUCCESS, items};
}

function view(items) {
  return { type: NOTIFICATION_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, pagination = false) {
        commit(loading(true));
        let page = '/notifications?pagination=' + pagination;

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
    }
};

const mutations = {
    [NOTIFICATION_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [NOTIFICATION_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [NOTIFICATION_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [NOTIFICATION_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [NOTIFICATION_LIST_RESET] (state) {
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
