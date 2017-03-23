<template>
  <div class="ui modal askedMatch">
    <i class="close icon"></i>
    <div class="header">
      Match details
    </div>
    <div class="image content">
      <img class="image" width="128" height="128" v-bind:src="match.user[match.region + '_profile'].avatar_url" v-if="match">
      <div class="description">  
        <div class="ui vertically divided center aligned grid" v-if="match">
          <div class="row">
            <div class="four wide column">
              <a class="ui label" v-bind:class="match.type | badge('color')">{{ match.type | badge('name') }}</a>
            </div>
            <div class="four wide column">
              <div v-if="match.type == 'comp'">
                <img v-bind:src="match.minRank | imageRank" alt="User's rank" width="32"> <b>{{ match.minRank }}</b>
                -
                <img v-bind:src="match.maxRank | imageRank" alt="User's rank" width="32"> <b>{{ match.maxRank }}</b>
              </div>
              <div v-else>
                {{ match.description }}
              </div>
            </div>
            <div class="four wide column">
              <a class="ui label" v-for="language in match.languages"><i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}</a>
            </div>
            <div class="four wide column">
              <i class="icon user" v-for="howMuch in match.howMuch"></i>
              <i class="icon user outline" v-for="howMuch in 5 - match.howMuch"></i>
            </div>
          </div>
          <div class="row">
            <div class="sixteen wide column">
              <div class="ui large" v-bind:class="{ buttons : match.invitationLink }">
                <button class="ui button copier" v-bind:data-clipboard-text="match.user.tag">Copy Battle.tag</button>
                <div class="or" data-text="&" v-if="match.invitationLink"></div>
                <button class="ui button" v-on:click="openDiscord(match)" v-if="match.invitationLink">Connect to discord</button>
              </div>
            </div>
          </div>
        </div>
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

    mounted () {
      if (window.overwatchLounge.askedMatch == '0') {
        this.error = 'No match like that found or it expired';
        $('.askedMatch').modal('show');
      } else if (window.overwatchLounge.askedMatch != null) {
        this.match = window.overwatchLounge.askedMatch;
        $('.askedMatch').modal('show');
      }
    }
  }
</script>