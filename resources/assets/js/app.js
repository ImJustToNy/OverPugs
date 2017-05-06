'use strict';

require('./bootstrap');

Echo = new Echo({
    broadcaster: 'pusher',
    key: OverPugs.pusherKey
});

Raven.config(OverPugs.sentry_dsn).addPlugin(RavenVue, Vue).install();

Vue.use(Vuex);
Vue.use(VueResource);

Vue.component('layout', require('./components/Layout'));
Vue.component('loading', require('./components/Loading'));
Vue.component('add', require('./components/Add'));
Vue.component('navbar', require('./components/Navbar'));
Vue.component('gamelist', require('./components/Gamelist'));
Vue.component('match', require('./components/Match'));
Vue.component('adsense', require('./components/AdSense'));

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
            profile: OverPugs.user ? OverPugs.user[OverPugs.user.prefered_region + '_profile'] : null,
            region: OverPugs.user ? OverPugs.user.prefered_region : 'us',
            userMatch: null,
            matches: []
        },

        actions: {
            switchLoading: function switchLoading(_ref, condition) {
                var commit = _ref.commit;

                commit('SWITCH_LOADING', condition);
            },
            changeRegion: function changeRegion(_ref2, condition) {
                var commit = _ref2.commit;

                commit('CHANGE_REGION', condition);
            },
            updateMatches: function updateMatches(_ref3) {
                var commit = _ref3.commit;
                var condition = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

                commit('UPDATE_MATCHES', condition);
            }
        },

        mutations: {
            SWITCH_LOADING: function SWITCH_LOADING(state, condition) {
                state.loading = condition;
            },
            CHANGE_REGION: function CHANGE_REGION(state, condition) {
                state.region = condition;

                if (state.user) {
                    state.profile = state.user[condition + '_profile'];

                    Vue.http.post('changeRegion', {
                        'region': condition
                    });
                }
            },
            UPDATE_MATCHES: function UPDATE_MATCHES(state, condition) {
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
            }
        }
    })
});