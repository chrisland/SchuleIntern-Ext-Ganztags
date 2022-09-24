<template>
  <div>

    <ModalSchueler></ModalSchueler>

    <div class="si-calendar">
      <div class="si-calendar-header">
        <button class="si-btn  chevron-left" @click="subtractWeek">
          <i class="fa fa-arrow-left"></i>Zurück
        </button>
        <button @click="gotoToday"
                class="si-btn si-btn-light">
          <i class="fa fa-home"></i>Heute
        </button>
        <div class="title"><span class="margin-r-l text-green">{{ room }}</span> <span class="margin-l-l">{{
            $date(firstDayOfWeek).format("DD.")
          }} - {{ $date(lastDayOfWeek).format("DD. MMMM YYYY") }}</span></div>
        <button class="si-btn chevron-right" @click="addWeek">
          <i class="fa fa-arrow-right"></i>Weiter
        </button>
      </div>


      <div class="">
        <div class="flex-row">
          <div v-bind:key="j" v-for="(date, j) in daysInWeek"
               class="flex-1" :class="{ 'bg-white padding-l-m': isToday(date) == true}">
            <div class="flex-row margin-t-m">
              <h4 :class="{ 'text-orange': isToday(date) == true}" class="flex-1 text-center">{{ $date(date).format('DD. dd') }}</h4>
              <button class="si-btn si-btn-icon si-btn-border flex-end margin-r-m" v-on:click="handlerPrintDay(date)"><i
                  class="fa fa-print"></i></button>
            </div>
            <div v-bind:key="j" v-for="(content, j) in getDayContent(date)" class="margin-r-m">

              <div v-if="content.type == 'group'" class="si-box">
                <h4><span class="blockInline width-2rem height-2rem margin-r-m"
                          :style="'background-color:'+content.color"></span>{{ content.title }}</h4>
                <div v-if="content.schueler">

                  <span
                      class=" text-grau bg-white border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold"
                      v-bind:style="{ color: content.color }"><i class="fa fa-child"></i> {{ content.schueler.length }}</span>
                </div>

                <button v-if="!content.leader" class="si-btn si-btn-green" v-on:click="handlerAddLeader(date, content)">
                  <i class="fa fa-plus"></i> Wer?
                </button>
              </div>

              <div v-if="content.type == 'day-group'" class="si-box"
                   :style="'border-color:'+content.color+'; border-left-width: 5px;'">
                <div class="flex-row">
                  <h4 class="flex-2">{{ content.title }}</h4>
                  <div v-if="content.schueler" class="text-right flex-1">
                    <button class="si-btn si-btn-border" v-bind:style="{ color: content.color }"
                            v-on:click="handlerOpenSchueler(content)">
                      {{ content.schueler.length }} <i class="fa fa-users"></i>
                    </button>
                    <!--<span class="text-grau bg-white border-radius padding-t-xs padding-b-xs padding-l-s padding-r-s margin-r-m text-bold"
                        v-bind:style="{ color: content.color }"><i class="fa fa-child"></i> {{content.schueler.length}}</span>-->
                  </div>
                </div>
                <div v-if="content.leader"><i class="fas fa-user"></i> {{ content.leader.name }}</div>
                <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
              </div>


              <div v-if="content.type == 'activity'" class="si-box">
                <h4><span v-if="content.color" class="blockInline width-2rem height-2rem margin-r-m"
                          :style="'background-color:'+content.color"></span>{{ content.title }}</h4>
                <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
                <div v-if="content.leader_id"><i class="fas fa-user"></i> {{ content.leader_id }}</div>
                <button v-if="!content.leader" class="si-btn si-btn-green" v-on:click="handlerAddLeader(date, content)">
                  <i class="fa fa-plus"></i> Leader
                </button>
              </div>

              <div v-if="content.type == 'day-activity'" class="si-box"
                   :style="'border-color:'+content.color+'; border-left-width: 5px;'">
                <h4><span v-if="content.color" class="blockInline width-2rem height-2rem margin-r-m"
                          :style="'background-color'+content.color"></span>{{ content.title }}</h4>
                <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
                <div v-if="content.leader"><i class="fas fa-user"></i> {{ content.leader.name }}</div>
              </div>

            </div>
            <button class="si-btn" v-on:click="handlerAddAktivity(date)"><i class="fa fa-plus"></i> Aktivity</button>


          </div>
        </div>
      </div>

      <hr>

    </div>


  </div>
</template>


<script>


// import User from "../mixins/User.vue";

import ModalSchueler from "../mixins/ModalSchueler.vue";

export default {
  components: {
    ModalSchueler
  },
  name: 'Calendar',
  props: {
    plan: [],
    acl: Object,
    room: String,
    showDays: Object,
    userSelf: Array
  },
  data() {
    return {

      today: this.$date(),
      thisWeek: false,

      prevDays: globals.prevDays,

      //plan: []

    }
  },
  mounted() {

  },
  created: function () {

    //this.plan = [];

    this.thisWeek = this.today;
    this.changedDate();

    this.prevDays = parseInt(this.prevDays);

    var that = this;
    EventBus.$on('calender--reload', data => {
      that.changedDate();
      EventBus.$emit('modal-item--close');
      EventBus.$emit('modal-form--close');
    });


  },
  computed: {


    firstDayOfWeek: function () {
      return this.thisWeek.startOf('week');
    },
    lastDayOfWeek: function () {
      return this.thisWeek.endOf('week');
    },
    daysInWeek: function () {
      var arr = [];
      var foo = this.firstDayOfWeek;
      for (let i = 0; i < 7; i++) {
        if (this.showDays && this.showDays[foo.format('dd')] == 1) {
          arr.push([foo]);
        }
        foo = foo.add(1, 'day');
      }
      return arr;
    }

  },
  methods: {

    handlerPrintDay: function (date) {

      if (date) {
        window.open('index.php?page=ext_ganztags&view=kalender&task=printDay&day=' + this.$date(date).format('YYYY-MM-DD'), '_blank').focus();
      }


    },
    handlerOpenSchueler: function (data) {
      //console.log(data.schueler);

      EventBus.$emit('modal-schueler--open', {
        schueler: data.schueler
      });


    },
    handlerAddLeader: function (date, item) {

      EventBus.$emit('modal-leader--open', {
        'day': this.$date(date).format('dd'),
        'content': this.getDayContent(date),
        'type': item.type,
        'preselect': item.leader_id,
        'callback': function (form) {

          if (form.leader && date && item.id) {
            EventBus.$emit('date--group', {
              'group': item,
              'leader': form.leader,
              'date': this.$date(date).format('YYYY-MM-DD')
            });
            EventBus.$emit('modal-leader--close');
          }

        }
      });


    },
    handlerAddAktivity: function (date) {
      EventBus.$emit('modal-aktivity--open', {
        'callback': function (form) {
          if (form.activity && form.leader && date) {
            EventBus.$emit('date--aktivity', {
              'activity': form.activity,
              'leader': form.leader,
              'date': this.$date(date).format('YYYY-MM-DD')
            });
            EventBus.$emit('modal-aktivity--close');
          }
        }
      });
    },
    getDayContent: function (date) {
      //console.log(this.plan);

      var ret = false;
      if (this.plan && typeof this.plan === 'object') {
        var day = this.$date(date).format('YYYY-MM-DD');
        for (const o in this.plan) {
          if (this.plan[o].date == day) {
            ret = this.plan[o].content;
          }
        }
      }
      return ret;
    },
    isToday: function (day) {
      if (this.today.isSame(day, 'day')) {
        return true;
      }
      return false;
    },
    isFuture: function (day) {
      if (this.today.isBefore(day, 'day')) {
        return true;
      }
      return false;
    },
    subtractWeek: function () {
      this.thisWeek = this.thisWeek.subtract(1, 'week');
      this.changedDate();
    },
    addWeek: function () {
      this.thisWeek = this.thisWeek.add(1, 'week');
      this.changedDate();
    },
    gotoToday: function () {
      this.thisWeek = this.today;
      this.changedDate();
    },
    changedDate: function () {
      EventBus.$emit('calendar--changedDate', {
        von: this.firstDayOfWeek.unix(),
        bis: this.lastDayOfWeek.unix()
      });
    }

  }
}
</script>
