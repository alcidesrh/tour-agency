import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  HTCTOUR_LIST_ERROR,
  HTCTOUR_LIST_LOADING,
  HTCTOUR_LIST_RESET,
  HTCTOUR_LIST_VIEW,
  HTCTOUR_LIST_SUCCESS,
  HTCTOUR_LIST_SUCCESS_ITEMS_LIST
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: [],
  itemsList: [],
};

function error(error) {
  return {type: HTCTOUR_LIST_ERROR, error};
}

function loading(loading) {
  return {type: HTCTOUR_LIST_LOADING, loading};
}

function success(items) {
  return {type: HTCTOUR_LIST_SUCCESS, items};
}

function successItemList(items) {
    return {type: HTCTOUR_LIST_SUCCESS_ITEMS_LIST, items};
}

function view(items) {
  return { type: HTCTOUR_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view,
  itemsList: state => state.itemsList
};

const actions = {
    getItems({ commit }, pagination = true) {
      let page = '/h_t_c_tours?pagination=' + pagination
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
          commit(error(time() + ' ' +e.message));
        });
    },
    getItemsList({ commit }, search = null, page = '/list-htc-tour') {
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
            });
    },
    reset({commit}) {
        commit(loading(false));
    }
};

const mutations = {
    [HTCTOUR_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [HTCTOUR_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [HTCTOUR_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [HTCTOUR_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [HTCTOUR_LIST_SUCCESS_ITEMS_LIST] (state, payload) {
        state.itemsList = payload.items;
    },
    [HTCTOUR_LIST_RESET] (state) {
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
