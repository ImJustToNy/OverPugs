require('./bootstrap');

Vue.use(Vuex);
Vue.use(VueResource);

Vue.component('layout', require('./components/layout'));
Vue.component('loading', require('./components/loading'));
Vue.component('add', require('./components/add.vue'));
Vue.component('sidebar', require('./components/sidebar'));
Vue.component('gamelist', require('./components/gamelist'));

Vue.http.options.root = '/api';
Vue.http.options.header = {
  'X-CSRF-TOKEN': window.overwatchLounge.csrfToken,
  'X-Requested-With': 'XMLHttpRequest'
}

Vue.filter('imageRank', function (value) {
  var imageId;

  if (value < 1499) {
    imageId = 1
  } else if (value < 1999) {
    imageId = 2
  } else if (value < 2499) {
    imageId = 3
  } else if (value < 2999) {
    imageId = 4
  } else if (value < 3499) {
    imageId = 5
  } else if (value < 3999) {
    imageId = 6
  } else if (value <= 5000) {
    imageId = 7
  }

  return 'https://blzgdapipro-a.akamaihd.net/game/rank-icons/season-2/rank-' + imageId + '.png';
})

Vue.filter('toUpperCase', function (value) {
  return value.toUpperCase();
})

Vue.filter('friendlyTag', function (value) {
  return value.substr(0, value.indexOf('#'));
})

new Vue({
  el: '#app',
  data: {
    a: 1
  },
  store: new Vuex.Store({
    state: {
      loading: true,
      user: window.overwatchLounge.user
    },
    actions: {
      switchLoading({ commit }, condition) {
        commit('SWITCH_LOADING', condition)
      }
    },
    mutations: {
      SWITCH_LOADING (state, condition) {
        state.loading = condition
      }
    }
  }),

  filters: {
    capitalize: function (value) {
      if (!value) return ''
      value = value.toString()
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
  }
});
