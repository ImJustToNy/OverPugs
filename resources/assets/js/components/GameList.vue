<template>
  <div>
    <adsense
            ad-client="ca-pub-1871612708734823"
            ad-slot="2535335992"
            ad-style="display: block"
            ad-format="auto">
    </adsense>

    <table class="ui table center aligned" v-show="matches.length">
      <thead>
        <tr>
          <td>
            <div class="ui mini input">
              <input type="text" placeholder="Nickname" v-model="filter.nickname">
            </div>
          </td>
          <td>
            <select class="ui fluid dropdown" v-model="filter.type">
              <option value="not"><a class="ui mini label">Game type</a></option>
              <option value="comp"><a class="ui mini red label">Competitive</a></option>
              <option value="qp"><a class="ui mini green label">Quick Play</a></option>
              <option value="custom"><a class="ui mini blue label">Custom games</a></option>
              <option value="brawl"><a class="ui mini yellow label">Brawl</a></option>
            </select>
          </td>
          <td>
            <div class="ui icon input">
              <input type="number" placeholder="Rank" min="0" max="5000" v-model="filter.rank">
              <i class="circular paste link icon" v-if="rank" v-on:click="grabProfileRank"></i>
            </div>
          </td>
          <td>
            <div class="ui mini input">
              <select class="ui search dropdown" v-model="filter.languages" multiple="">
                <option value="">Languages</option>
                <option v-bind:value="key" v-for="(language, key) in languages">
                  <i class="flag" v-bind:class="key"></i> {{ language }}
                </option>
              </select>
            </div>
          </td>
          <td>
            <select class="ui dropdown" v-model="filter.howMuch">
              <option value="0">Player count</option>
              <option v-bind:value="loop" v-for="loop in 11">
                <i class="icon user"></i>
                {{ loop | pluralize('player') }}
              </option>
            </select>
          </td>
          <td>
            <select class="ui mini fluid dropdown" v-model="filter.discord">
              <option value="not">Discord?</option>
              <option value="true">With discord</option>
              <option value="false">Without discord</option>
            </select>
          </td>
        </tr>
      </thead>
      <tbody>
        <game
              v-for="game in matches"
              v-show="game.region == region"
              v-bind:class="{ warning: user && (game.user.tag == user.tag) }"
              v-if="check(game)" 
              v-bind:match="game">
        </game>
      </tbody>
    </table>
    <div v-show="!matches.length">
      <h2 class="ui center aligned icon header disabled">
        <i class="frown icon"></i>
        No matches found
      </h2>
    </div>
    
    <adsense
            ad-client="ca-pub-1871612708734823"
            ad-slot="1512601197"
            ad-style="display: block"
            ad-format="auto">
    </adsense>
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

      rank () {
        return this.$store.state.rank
      },

      matches () {
        return this.$store.state.matches
      }
    },

    data: function () {
      return {
        filter: {
          nickname: '',
          type: 'not',
          rank: 0,
          languages: [],
          howMuch: 0,
          discord: 'not',
        },
        languages: OverPugs.languages
      }
    },

    methods: {
      openDiscord: function (game) {
        open(game.invitationLink, "", "width=450,height=500")
      },

      setupMatchRefresher: function () {
        Echo.channel('matches')
          .listen('NewMatch', (e) => {
            var matches = this.matches;
            matches.push(e.match);

            this.$store.dispatch('updateMatches', matches);
          })
          .listen('UpdateExpire', (e) => {
            var matches = this.matches;

            matches.forEach(function (item, key) {
              if (item.id == e.match.id) {
                matches[key] = e.match;
              }
            })

            this.$store.dispatch('updateMatches', matches);
          })
          .listen('DeleteMatch', (e) => {
            var matches = this.matches;

            matches.forEach(function (item, key) {
              if (item.id == e.id) {
                matches.splice(matches.indexOf(key), 1);
              }
            })

            this.$store.dispatch('updateMatches', matches);
          })
      },

      getMatches: function () {
        this.$http.get('match/list').then(response => {
          this.$store.dispatch('switchLoading', false)
          this.$store.dispatch('updateMatches', response.body);
        });
      },

      check: function (game) {
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

        if (this.filter.discord != 'not' && !game.invitationLink == (this.filter.discord == 'true')) {
          return false;
        }

        return true;
      },

      grabProfileRank: function () {
        this.filter.rank = this.rank;
      }
    },

    mounted () {
      this.getMatches();
      this.setupMatchRefresher();

      new Clipboard('.copier');
    }
  }
</script>
