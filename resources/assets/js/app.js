'use strict';

require('./bootstrap');

Echo = new Echo({
    broadcaster: 'pusher',
    key: OverPugs.pusherKey,
    namespace: 'OverPugs.Events'
});

Raven.config(OverPugs.sentry_dsn, {
  shouldSendCallback: function () {
    return OverPugs.production;
  }
}).addPlugin(RavenVue, Vue).install();

Vue.use(Vuex);
Vue.use(VueResource);

Vue.component('layout', require('./components/Layout'));
Vue.component('loading', require('./components/Loading'));
Vue.component('add', require('./components/Add'));
Vue.component('navbar', require('./components/Navbar'));
Vue.component('game', require('./components/Game'));
Vue.component('gamelist', require('./components/GameList'));
Vue.component('gamemodal', require('./components/GameModal'));
Vue.component('adsense', VueAdsense);

Vue.filter('imageRank', function (value) {
    var imageId;

    if (value < 1499) {
        imageId = 1;
    } else if (value < 1999) {
        imageId = 2;
    } else if (value < 2499) {
        imageId = 3;
    } else if (value < 2999) {
        imageId = 4;
    } else if (value < 3499) {
        imageId = 5;
    } else if (value < 3999) {
        imageId = 6;
    } else if (value <= 5000) {
        imageId = 7;
    }

    return 'https://blzgdapipro-a.akamaihd.net/game/rank-icons/season-2/rank-' + imageId + '.png';
});

Vue.filter('toUpperCase', function (value) {
    return value.toUpperCase();
});

Vue.filter('toLowerCase', function (value) {
    return value.toLowerCase();
});

Vue.filter('friendlyTag', function (value) {
    return value.substr(0, value.indexOf('#'));
});

Vue.filter('pluralize', function (value, word) {
    return value + ' ' + word + (value > 1 ? 's' : '');
});

Vue.filter('badge', function (value, property) {
    var types = {
        'qp': {
            'name': 'Quick Play',
            'color': 'blue'
        },
        'comp': {
            'name': 'Competitive',
            'color': 'red'
        },
        'custom': {
            'name': 'Custom games',
            'color': 'green'
        },
        'brawl': {
            'name': 'Brawl',
            'color': 'yellow'
        }
    };

    return types[value][property];
});

Vue.http.options.root = '/api';
Vue.http.options.headers = {
    'X-CSRF-TOKEN': OverPugs.csrfToken,
    'X-Requested-With': 'XMLHttpRequest'
};

new Vue({
    el: '#app',
    store: new Vuex.Store({
        state: {
            loading: true,
            user: OverPugs.user,
            avatar_url: OverPugs.user ? OverPugs.user.avatar_url : null,
            rank: OverPugs.user ? OverPugs.user.rank : null,
            region: OverPugs.user ? OverPugs.user.prefered_region : 'us',
            userMatch: null,
            matches: []
        },

        actions: {
            switchLoading({ commit }, condition) {
                commit('SWITCH_LOADING', condition);
            },

            changeRegion({ commit }, condition) {
                commit('CHANGE_REGION', condition);
            },

            updateMatches({ commit }, condition = null) {
                commit('UPDATE_MATCHES', condition);
            },

            updateProfile({ commit }, condition) {
                commit('UPDATE_PROFILE', condition);
            }
        },

        mutations: {
            SWITCH_LOADING(state, condition) {
                state.loading = condition;
            },

            CHANGE_REGION(state, condition) {
                state.region = condition;

                if (state.user) {
                    Vue.http.post('changeRegion', {
                        'region': condition
                    });
                }
            },

            UPDATE_MATCHES(state, condition) {
                if (condition == null) {
                    condition = state.matches;
                }

                state.userMatch = null;

                condition.forEach(function (i, k) {
                    if (moment.utc(i.expireAt).diff(moment(), 'seconds') < 1) {
                        condition.splice(condition.indexOf(k), 1);
                    } else if (state.user && state.user.tag == i.user.tag) {
                        state.userMatch = i;
                    }
                });

                state.matches = condition;
            },

            UPDATE_PROFILE(state, condition) {
                state.rank = condition.rank;
                state.avatar_url = condition.avatar_url;
            }
        }
    })
});