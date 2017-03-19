<template>
  <table class="ui celled table center aligned">
    <tbody>
      <tr v-for="game in games">
        <td>
          <img class="ui avatar image" src="https://blzgdapipro-a.akamaihd.net/game/unlocks/0x0250000000000D76.png">{{ game.user.tag }}
        </td>
        <td>
          <img v-bind:src="game.rank | image" alt="User's rank" width="16"> <b>{{ game.rank }}</b>
        </td>
        <td>
          <a class="ui label" v-for="language in game.languages"><i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}</a>
        </td>
        <td>
          <i class="icon user outline" v-for="howMuch in game.howMuch"></i>
        </td>
        <td>
          <div class="ui buttons">
            <div class="ui button" v-on:click="openDiscord(game)">
              <img src="/images/discord.png" alt="Discord logo" class="icon">Connect to discord channel
            </div>
            <div class="or" data-text="&"></div>
            <div class="ui button copier" v-on:click="" v-bind:data-clipboard-text="game.user.tag">
              <img src="/images/bnet.png" alt="Battle.net logo" class="icon"> Copy BattleTag
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
  export default {
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
      this.$http.get('matches').then(response => {
        this.games = response.body;

        this.$store.dispatch('switchLoading', false)
      }, response => {
        console.log(response);
      });

      new Clipboard('.copier');
    }
  }
</script>