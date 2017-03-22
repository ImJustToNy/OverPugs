<template>
  <div class="ui fixed stackable menu">
    <div class="ui container">
      <a href="#" class="header item">
        <img class="logo" src="/images/logo.png" width="128">
        OverSearch
      </a>
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
          <div v-if="!match" class="ui green button" v-on:click="openAddModal"><i class="icon plus"></i> Add new game</div>
          <div v-else>Expires in <b>{{ expireTimer | formatMSS }}</b>
            <div class="ui mini compact blue icon button" v-on:click="refreshMatch($event)" data-tooltip="Refresh your match" data-position="bottom center">
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

      match () {
        return this.$store.state.match
      },

      profile () {
        return this.$store.state.profile
      },

      serverTime () {
        return this.$store.state.serverTime
      },

      expireTimer () {
        var $this = this;
        return moment(moment(this.match.expireAt).diff(moment(this.serverTime))).add(-this.diffrence, 'seconds');
      }
    },

    watch: {
      match: function (newMatch) {
        if (!newMatch) {
          this.diffrence = 0;
        }
      }
    },

    filters: {
      formatMSS: function (value) {
        return moment(value).format('m:ss');
      }
    },

    data () {
      return {
        diffrence: 0,
        canRefresh: false,
        toAdd: 1
      }
    },

    mounted () {
      var $this = this;

      $('.ui.dropdown').dropdown();

      document.addEventListener("visibilitychange", function() {
        $this.toAdd = (document.visibilityState == 'visible') ? 1 : 1.5;
      });

      this.setupRefresher();
    },

    methods: {
      setupRefresher: function () {
        var $this = this;

        setInterval(function() {
          if ($this.match) {
            if (($this.expireTimer.seconds() < 1 && $this.expireTimer.minutes() < 1) || $this.expireTimer.minutes() > 30) {
              $this.newMatch(null);
            } else {
              $this.canRefresh = ($this.expireTimer.minutes() <= 3);
              $this.diffrence += $this.toAdd;
            }
          }
        }, 1000)
      },

      deleteMatch: function (event) {
        this.$http.post('match/delete')
          .then(response => {
            this.newMatch(null)
          }, response => {
            $(event.target).transition('shake');
          })
      },

      refreshMatch: function (event) {
        this.$http.post('match/refresh')
          .then(response => {
            this.canRefresh = false
            this.diffrence = 0
            this.$store.dispatch('setServerTime', response.body.serverTime)
            this.newMatch(response.body.match)
          }, response => {
            $(event.target).transition('shake');
          })
      },

      newMatch: function (match) {
        this.$store.dispatch('newMatch', match)
      },

      switchRegion: function (region) {
        this.$store.dispatch('changeRegion', region)
      },

      openAddModal: function () {
        if (!this.user) {
          window.location.href = '/login';
        } else {
          $('.ui.modal').modal({
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