<template>
  <div class="ui modal askedMatch">
    <i class="close icon"></i>
    <div class="header">
      Match details
    </div>
    <div class="content">
      <div class="description">  
        <table class="ui table center aligned" v-if="match">
          <tbody>
            <tr>
              <td>
                <img class="ui avatar image" v-bind:src="match.user[match.region + '_profile'].avatar_url"> {{ match.user.tag | friendlyTag }}
              </td>
              <td>
                <a class="ui mini label" v-bind:class="match.type | badge('color')">{{ match.type | badge('name') }}</a>
              </td>
              <td v-if="match.type == 'comp'">
                <img v-bind:src="match.minRank | imageRank" alt="User's rank" width="16"> <b>{{ match.minRank }}</b>
                -
                <img v-bind:src="match.maxRank | imageRank" alt="User's rank" width="16"> <b>{{ match.maxRank }}</b>
              </td>
              <td v-else>
                {{ match.description }}
              </td>
              <td>
                <a class="ui label" v-for="language in match.languages"><i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}</a>
              </td>
              <td>
                <i class="icon user" v-for="howMuch in match.howMuch"></i>
                <i class="icon user outline" v-for="howMuch in 5 - match.howMuch"></i>
              </td>
              <td>
                <div class="ui circular button icon" v-on:click="openDiscord(match)" v-if="match.invitationLink" data-tooltip="Connect to discord">
                  <img src="/images/discord.png" alt="Discord logo" class="icon">
                </div>
                <div class="ui circular button icon copier" v-bind:data-clipboard-text="match.user.tag" data-tooltip="Copy Battle.net tag">
                  <img src="/images/bnet.png" alt="Battle.net logo" class="icon">
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <h2 class="ui center aligned icon header disabled" v-else>
          <i class="clock icon"></i>
          {{ error }}
        </h2>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        match: false,
        error: false
      }
    },

    methods: {
      openDiscord: function (match) {
        open(match.invitationLink, "", "width=450,height=500")
      }
    },

    beforeMount () {
      if (window.overwatchLounge.askedMatch == '0') {
        this.error = 'No lobby like that found or it expired';
      } else if (window.overwatchLounge.askedMatch != null) {
        this.match = window.overwatchLounge.askedMatch;
      }
    },

    mounted () {
      if (this.match != false || this.error != false) {
        $('.askedMatch').modal('show');
      }
    }
  }
</script>