<template>
    <tr>
        <td>
            <img class="ui avatar image" v-bind:src="match.user.avatar_url"> {{ match.user.tag | friendlyTag }}
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
            <a class="ui label" v-for="language in match.languages">
                <i class="flag" v-bind:class="language"></i> {{ language | toUpperCase }}
            </a>
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
</template>

<script>
  export default {
    props: ['match'],

    methods: {
      openDiscord: function (match) {
        open(match.invitationLink, "", "width=450,height=500")
      }
    },
  }
</script>