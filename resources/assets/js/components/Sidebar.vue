<template>
  <div class="ui fixed stackable menu">
    <div class="ui container">
      <div class="header item">
        <img class="logo" src="/images/logo.png" width="128">
        OverSearch
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
        <div class="item" v-if="user">
          <img v-bind:src="profile.avatar_url" alt="User's avatar" class="logo" v-if="profile"> {{ user.tag | friendlyTag }} 
        </div>
        <div class="item" v-if="profile">
          <a href="/logout" class="ui icon button" data-tooltip="Logout" data-position="bottom center">
            <i class="icon sign out"></i>
          </a>
        </div>
        <div class="item" v-if="profile">
          <img v-bind:src="profile.rank | imageRank" alt="User's rank"> {{ profile.rank }}
        </div>
        <a class="item">
          <div v-if="!userMatch" class="ui green button" v-on:click="openAddModal"><i class="icon plus"></i> Add new game</div>
          <div v-else>Expires in <b>{{ diff }}</b>
            <div class="ui mini compact blue icon button" v-on:click="refreshMatch($event)" v-if="diff <= 180" data-tooltip="Refresh your match" data-position="bottom center">
              <i class="icon refresh"></i>
            </div>
            <div class="ui mini compact red icon button" v-on:click="deleteMatch($event)" data-tooltip="Delete your match" data-position="bottom center">
              <i class="icon delete"></i>
            </div>
          </div>
        </a>
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

      profile () {
        return this.$store.state.profile
      }
    },

    filters: {
      formatSeconds: function (value) {
        var time = moment.duration(value, 'seconds');
        return time.minutes() + ':' + time.seconds();
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
        this.$http.post('match/delete')
          .then(response => {}, response => {
            $(event.target).transition('shake');
          })
      },

      refreshMatch: function (event) {
        this.canRefresh = false;

        this.$http.post('match/refresh')
          .then(response => {}, response => {
            $(event.target).transition('shake');
          })
      },

      switchRegion: function (region) {
        this.$store.dispatch('changeRegion', region)
      },

      openAddModal: function () {
        if (!this.user) {
          window.location.href = '/login';
        } else {
          $('.addModal').modal({
            onApprove: function () {
              return false;
            },
            
            closable: false
          }).modal('show');
        }
      }
    }
  }
</script>