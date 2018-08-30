import fetch from '../../../utils/fetch';
import {
  BOOKING_SHOW_ERROR,
  BOOKING_SHOW_LOADING,
  BOOKING_SHOW_RETRIEVED_SUCCESS,
  BOOKING_SHOW_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  retrieved: null
};

function error(error) {
  return {type: BOOKING_SHOW_ERROR, error};
}

function loading(loading) {
  return {type: BOOKING_SHOW_LOADING, loading};
}

function retrieved(retrieved) {
  return {type: BOOKING_SHOW_RETRIEVED_SUCCESS, retrieved};
}

function reset() {
  return {type: BOOKING_SHOW_RESET};
}

const getters = {
  error: state => state.error,
  loading: state => state.loading,
  item: state => state.retrieved
};

const actions = {
  retrieve({ commit }, id) {
    commit(loading(true));

    return fetch(id)
      .then(response => response.json())
      .then(data => {
        commit(loading(false));
        commit(retrieved(data));
      })
      .catch(e => {
        commit(loading(false));
        commit(error(e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [BOOKING_SHOW_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [BOOKING_SHOW_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [BOOKING_SHOW_RETRIEVED_SUCCESS] (state, payload) {
      state.retrieved = payload.retrieved;
    },
    [BOOKING_SHOW_RESET] (state) {
      state.retrieved = null;
    }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
