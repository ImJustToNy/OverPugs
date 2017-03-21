require('./bootstrap');

Vue.use(Vuex);
Vue.use(VueResource);

Vue.component('layout', require('./components/layout'));
Vue.component('loading', require('./components/loading'));
Vue.component('add', require('./components/add'));
Vue.component('sidebar', require('./components/sidebar'));
Vue.component('gamelist', require('./components/gamelist'));

Vue.http.options.root = '/api';
Vue.http.options.headers = {
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

Vue.filter('toLowerCase', function (value) {
  return value.toLowerCase();
})

Vue.filter('friendlyTag', function (value) {
  return value.substr(0, value.indexOf('#'));
})

Vue.filter('pluralize', function (value, word) {
  return value + ' ' + word + ((value > 1) ? 's' : '');
})

new Vue({
  el: '#app',
  store: new Vuex.Store({
    state: {
      loading: true,
      user: window.overwatchLounge.user,
      profile: ((window.overwatchLounge.user) ? window.overwatchLounge.user[window.overwatchLounge.user.prefered_region + '_profile'] : null),
      region: ((window.overwatchLounge.user) ? window.overwatchLounge.user.prefered_region : 'us'),
      match: window.overwatchLounge.match,
      serverTime: window.overwatchLounge.serverTime
    },

    actions: {
      switchLoading({ commit }, condition) {
        commit('SWITCH_LOADING', condition)
      },

      changeRegion({ commit }, condition) {
        commit('CHANGE_REGION', condition)
      },

      newMatch({ commit }, condition) {
        commit('NEW_MATCH', condition)
      },

      setServerTime({ commit }, condition) {
        commit('SET_SERVERTIME', condition)
      }
    },

    mutations: {
      SWITCH_LOADING (state, condition) {
        state.loading = condition
      },

      NEW_MATCH (state, condition) {
        state.match = condition;
      },

      CHANGE_REGION (state, condition) {
        state.region = condition;

        if (state.user) {
          state.profile = state.user[condition + '_profile'];
          
          Vue.http.post('changeRegion', {
            'region': condition
          })
        }
      },

      SET_SERVERTIME (state, condition) {
        state.serverTime = condition;
      }
    }
  })
});
