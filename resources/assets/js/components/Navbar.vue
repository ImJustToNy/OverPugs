<template>
  <div class="ui fixed stackable menu">
    <div class="ui container">
      <div class="header item">
        <img class="logo" src="/images/logo.png" width="128">
        OverPugs
        <div class="ui left pointing red basic label">
          Made with <i class="icon red heart nomargin"></i> by <a href="mailto:imtony@protonmail.com">ToNy</a>
        </div>
      </div>
      <div class="right green menu">
        <div class="ui dropdown item">
          <i class="flag" v-bind:class="region"></i> {{ region | toUpperCase }} <i class="dropdown icon"></i>
          <div class="menu">
            <a class="item" v-on:click="switchRegion('us')"><i class="flag us"></i> North America</a>
            <a class="item" v-on:click="switchRegion('eu')"><i class="flag eu"></i> Europe</a>
            <a class="item" v-on:click="switchRegion('kr')"><i class="flag kr"></i> Korea</a>
          </div>
        </div>
        <div class="ui dropdown item" v-if="user">
          <img v-bind:src="avatar_url" class="logo" alt="User's avatar"> {{ user.tag | friendlyTag }} <i class="dropdown icon"></i>
          <div class="menu">
            <a v-on:click="refreshProfile" class="item">
              <i class="icon refresh"></i>
              <span class="text">Refresh your rank</span>
            </a>
          </div>
        </div>
        <div class="ui dropdown item" v-if="user">
          <img v-bind:src="user.discord_avatar_url" class="logo" alt="Discord avatar"> {{ user.discord_nickname }} <i class="dropdown icon"></i>
          <div class="menu">
            <a href="/login/discord" class="item">
              <i class="icon refresh"></i>
              <span class="text">Reconnect your discord account</span>
            </a>
          </div>
        </div>
        <div class="item" v-if="user">
          <a href="/logout" class="ui icon button" data-tooltip="Logout" data-position="bottom center">
            <i class="icon sign out"></i>
          </a>
        </div>
        <div class="item" v-if="user">
          <img v-bind:src="rank | imageRank" alt="User's rank"> {{ rank }}
        </div>
        <div class="item">
          <div v-if="!user" class="ui blue button" v-on:click="redirectToLogin"><i class="icon sign in"></i> Login</div>
          <div v-if="!userMatch && user" class="ui green button" v-on:click="openAddModal"><i class="icon plus"></i> Add new game</div>
          <div v-if="user && userMatch">Expires in <b>{{ diff | formatSeconds }}</b>
            <div class="ui mini compact blue icon button" v-on:click="refreshMatch($event)" v-if="diff <= 180" data-tooltip="Refresh your match" data-position="bottom center">
              <i class="icon refresh"></i>
            </div>
            <div class="ui mini compact red icon button" v-on:click="deleteMatch($event)" data-tooltip="Delete your match" data-position="bottom center">
              <i class="icon delete"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      user () {
        return this.$store.state.user
      },

      region () {
        return this.$store.state.region
      },

      userMatch () {
        return this.$store.state.userMatch
      },

      rank () {
        return this.$store.state.rank
      },

      avatar_url () {
        return this.$store.state.avatar_url
      }
    },

    filters: {
      formatSeconds: function (value) {
        var time = moment.duration(value, 'seconds');
        return time.minutes() + ':' + ('0' + time.seconds()).slice(-2);
      }
    },

    data () {
      return {
        diff: 0,
        canRefresh: false
      }
    },

    mounted () {
      $('.ui.dropdown').dropdown();

      this.setupRefresher();
    },

    methods: {
      setupRefresher: function () {
        var $this = this;

        setInterval(function() {
          $this.$store.dispatch('updateMatches');

          if ($this.userMatch) {
            $this.diff = moment.utc($this.userMatch.expireAt).diff(moment(), 'seconds');
          }
        }, 1000);
      },

      deleteMatch: function (event) {
        this.$store.dispatch('switchLoading', true)

        this.$http.post('match/delete')
          .then(response => {}, response => {
            $(event.target).transition('shake');
          }).then(() => {
            this.$store.dispatch('switchLoading', false)
          }
        )
      },

      refreshMatch: function (event) {
        this.canRefresh = false;
        this.$store.dispatch('switchLoading', true);

        this.$http.post('match/refresh')
          .then(response => {}, response => {
            $(event.target).transition('shake');
          }).then(() => {
            this.$store.dispatch('switchLoading', false)
          }
        )
      },

      switchRegion: function (region) {
        this.$store.dispatch('changeRegion', region)
      },

      openAddModal: function () {
        $('.addModal').modal({
          onApprove: function () {
            return false;
          },
          
          closable: false
        }).modal('show');
      },

      redirectToLogin: function () {
        window.location.href = '/login';
      },

      refreshProfile: function () {
        this.$store.dispatch('switchLoading', true);

        this.$http.post('refreshProfile')
          .then(response => {
            this.$store.dispatch('updateProfile', response.data)
          })
          .then(() => {
            this.$store.dispatch('switchLoading', false);
          })
      }
    }
  }
</script>