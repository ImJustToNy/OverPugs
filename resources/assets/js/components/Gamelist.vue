<template>
  <div>
    <table class="ui table center aligned">
      <thead>
        <tr>
          <td>
            <div class="ui mini input">
              <input type="text" placeholder="Nickname" v-model="filter.nickname">
            </div>
          </td>
          <td>
            <select class="ui fluid dropdown" v-model="filter.type">
              <option value="not"><a class="ui mini label">Not selected</a></option>
              <option value="comp"><a class="ui mini red label">Competive</a></option>
              <option value="qp"><a class="ui mini green label">Quick Play</a></option>
              <option value="custom"><a class="ui mini blue label">Custom Games</a></option>
              <option value="brawl"><a class="ui mini yellow label">Brawl</a></option>
            </select>
          </td>
          <td>
            <div class="ui icon input">
              <input type="number" placeholder="Rank" min="0" max="5000" v-model="filter.rank">
              <i class="circular user link icon" v-if="profile" v-on:click="grabProfileRank"></i>
            </div>
          </td>
          <td>
            <div class="ui mini input">
              <select class="ui search dropdown" v-model="filter.languages" multiple="">
                <option value="">Languages</option>
                <option value="us"><i class="flag us"></i> English</option>
                <option value="de"><i class="flag de"></i> German</option>
                <option value="pl"><i class="flag pl"></i> Polish</option>
              </select>
            </div>
          </td>
          <td>
            <select class="ui dropdown" v-model="filter.howMuch">
              <option value="0">Not selected</option>
              <option v-bind:value="loop" v-for="loop in 5">
                {{ loop }}
              </option>
            </select>
          </td>
          <td>
            <select class="ui mini fluid dropdown" v-model="filter.discord">
              <option value="not">Not selected</option>
              <option value="true">With discord</option>
              <option value="false">Without discord</option>
            </select>
          </td>
        </tr>
      </thead>
      <tbody>
        <tr v-for="game in games" v-show="game.region == region" v-bind:class="{ warning: game.user.tag == user.tag }" v-if="check(game)">
          <td>
            <img class="ui avatar image" v-bind:src="game.user[game.region + '_profile'].avatar_url">{{ game.user.tag | friendlyTag }}
          </td>
          <td>
            <a class="ui mini label" v-bind:class="game.type | badge('color')">{{ game.type | badge('name') }}</a>
          </td>
          <td v-if="game.type == 'comp'">
            <img v-bind:src="game.minRank | imageRank" alt="User's rank" width="16"> <b>{{ game.minRank }}</b>
            -
            <img v-bind:src="game.maxRank | imageRank" alt="User's rank" width="16"> <b>{{ game.maxRank }}</b>
          </td>
          <td v-else>
            {{ game.description }}
          </td>
          <td>
            <a class="ui label" v-for="language in game.languages"><i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}</a>
          </td>
          <td>
            <i class="icon user" v-for="howMuch in game.howMuch"></i>
            <i class="icon user outline" v-for="howMuch in 5 - game.howMuch"></i>
          </td>
          <td>
            <div class="ui circular button icon" v-on:click="openDiscord(game)" v-if="game.invitationLink" data-tooltip="Connect to discord">
              <img src="/images/discord.png" alt="Discord logo" class="icon">
            </div>
            <div class="ui circular button icon copier" v-bind:data-clipboard-text="game.user.tag" data-tooltip="Copy Battle.net tag">
              <img src="/images/bnet.png" alt="Battle.net logo" class="icon">
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
  export default {
    computed: {
      region () {
        return this.$store.state.region
      },

      user () {
        return this.$store.state.user
      },

      profile () {
        return this.$store.state.profile
      },

      serverTime () {
        return this.$store.state.serverTime
      }
    },

    data: function () {
      return {
        games: [],
        filter: {
          nickname: '',
          type: 'not',
          rank: 0,
          languages: [],
          howMuch: 0,
          discord: 'not'
        }
      }
    },

    filters: {
      badge: function (value, property) {
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
        }
        return types[value][property];
      }
    },

    methods: {
      openDiscord: function (game) {
        open(game.invitationLink, "", "width=450,height=500")
      },

      setupMatchRefresher: function () {
        Echo.channel('matches')
          .listen('.OverwatchLounge.Events.NewMatch', (e) => {
            this.games.push(e.match);
          })
          .listen('.OverwatchLounge.Events.UpdateExpire', (e) => {
            var $this = this;

            $this.games.forEach(function (item, key) {
              if (item.id == e.match.id) {
                $this.games[key] = e.match;
              }
            })
          })
      },

      getMatches: function () {
        this.$http.get('match/list').then(response => {
          this.games = response.body;
        });

        this.$store.dispatch('switchLoading', false)
      },

      check: function (game) {
        if (moment(moment(game.expireAt).diff(moment(this.serverTime))).seconds() < 1) {
          return false;
        }

        if (this.filter.nickname != '' && !game.user.tag.toLowerCase().includes(this.filter.nickname.toLowerCase())) {
          return false;
        }

        if (this.filter.type != 'not' && game.type != this.filter.type) {
          return false;
        }

        if ((this.filter.rank != 0 && game.type == 'comp') && this.filter.rank <= game.minRank || this.filter.rank >= game.maxRank) {
          return false;
        }

        if (this.filter.languages.length) {
          var a = this.filter.languages,
            b = game.languages;

          var ai = 0,
            bi = 0;
          var result = [];

          while (ai < a.length && bi < b.length) {
            if (a[ai] < b[bi]) {
              ai++;
            } else if (a[ai] > b[bi]) {
              bi++;
            } else {
              result.push(a[ai]);
              ai++;
              bi++;
            }
          }

          if (result.length < 1) {
            return false;
          }
        }

        if (this.filter.howMuch != 0 && game.howMuch != this.filter.howMuch) {
          return false;
        }

        if (this.filter.discord != 'not' && (!game.invitationLink ? false : true) != (this.filter.discord == 'true' ? true : false)) {
          return false;
        }

        return true;
      },

      grabProfileRank: function () {
        this.filter.rank = this.profile.rank;
      }
    },

    mounted () {
      this.getMatches();
      this.setupMatchRefresher();

      new Clipboard('.copier');
    }
  }
</script>