<template>
  <div>
<!--     <h1>Your game</h1>
    <table class="ui celled table center aligned">
      <tbody>
        
      </tbody>
    </table> -->
    <table class="ui celled table center aligned">
      <tbody>
        <tr v-for="game in games" v-show="game.region == region">
          <td>
            <img class="ui avatar image" v-bind:src="game.user[game.region + '_profile'].avatar_url">{{ game.user.tag | friendlyTag }}
          </td>
          <td>
            <a class="ui label"><i class="flag" v-bind:class="game.region"></i> {{ game.region | toUpperCase }}</a>
          </td>
          <td>
            <img v-bind:src="game.minRank | imageRank" alt="User's rank" width="16"> <b>{{ game.minRank }}</b>
            -
            <img v-bind:src="game.maxRank | imageRank" alt="User's rank" width="16"> <b>{{ game.maxRank }}</b>
          </td>
          <td>
            <a class="ui label" v-for="language in game.languages"><i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}</a>
          </td>
          <td>
            <i class="icon user" v-for="howMuch in game.howMuch"></i>
            <i class="icon user outline" v-for="howMuch in 5 - game.howMuch"></i>
          </td>
          <td>
            <div class="ui buttons">
              <div class="ui button" v-on:click="openDiscord(game)">
                <img src="/images/discord.png" alt="Discord logo" class="icon">Connect to discord channel
              </div>
              <div class="or" data-text="&"></div>
              <div class="ui button copier" v-bind:data-clipboard-text="game.user.tag">
                <img src="/images/bnet.png" alt="Battle.net logo" class="icon"> Copy BattleTag
              </div>
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
      }
    },

    data: function () {
      return {
        games: []
      }
    },

    methods: {
      openDiscord: function (game) {
        open(game.invitationLink, "", "width=450,height=500")
      }
    },

    mounted () {
      this.$http.get('match/list').then(response => {
        this.games = response.body;

        this.$store.dispatch('switchLoading', false)
      }, response => {
        console.log(response);
      });

      new Clipboard('.copier');
    }
  }
</script>