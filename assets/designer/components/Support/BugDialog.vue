<template>
  <jinya-form @submit="submit">
    <jinya-modal :is-fullscreen="true" @close="$emit('close')" title="support.bug_dialog.title" v-if="show">
      <jinya-message :message="message" :state="state" class="is--bug-dialog" slot="message">
        <jinya-message-action-bar v-if="state === 'error'">
          <jinya-button href="mailto:developers@jinya.de" label="support.bug_dialog.send_mail"/>
        </jinya-message-action-bar>
      </jinya-message>
      <jinya-input :required="true" :validation-message="'support.bug_dialog.form.title.empty'|jvalidator"
                   label="support.bug_dialog.form.title"
                   v-model="title"/>
      <jinya-textarea :required="true" :validation-message="'support.bug_dialog.form.details.empty'|jvalidator"
                      label="support.bug_dialog.form.details"
                      v-model="details"/>
      <jinya-textarea :required="true" :validation-message="'support.bug_dialog.form.reproduce.empty'|jvalidator"
                      label="support.bug_dialog.form.reproduce"
                      v-model="reproduce"/>
      <jinya-choice :choices="severityLevels" :selected="severity"
                    @selected="severity = $event" label="support.bug_dialog.form.severity"/>
      <jinya-modal-button :closes-modal="true" :is-secondary="true" label="support.bug_dialog.form.cancel"
                          slot="buttons-left"/>
      <jinya-modal-button :is-success="true" label="support.bug_dialog.form.submit" slot="buttons-right" type="submit"/>
    </jinya-modal>
  </jinya-form>
</template>

<script>
  import JinyaModal from '@/framework/Markup/Modal/Modal';
  import JinyaModalButton from '@/framework/Markup/Modal/ModalButton';
  import JinyaTextarea from '@/framework/Markup/Form/Textarea';
  import JinyaInput from '@/framework/Markup/Form/Input';
  import JinyaChoice from '@/framework/Markup/Form/Choice';
  import Translator from '@/framework/i18n/Translator';
  import JinyaMessage from '@/framework/Markup/Validation/Message';
  import JinyaRequest from '@/framework/Ajax/JinyaRequest';
  import JinyaMessageActionBar from '@/framework/Markup/Validation/MessageActionBar';
  import JinyaButton from '@/framework/Markup/Button';
  import JinyaForm from '@/framework/Markup/Form/Form';

  export default {
    name: 'jinya-bug-dialog',
    components: {
      JinyaForm,
      JinyaButton,
      JinyaMessageActionBar,
      JinyaMessage,
      JinyaChoice,
      JinyaInput,
      JinyaTextarea,
      JinyaModalButton,
      JinyaModal,
    },
    props: {
      show: {
        type: Boolean,
        default() {
          return false;
        },
      },
    },
    computed: {
      severityLevels() {
        return [
          {
            value: 1,
            text: Translator.message('support.bug_dialog.form.severity_levels.slightly_annoying'),
          },
          {
            value: 2,
            text: Translator.message('support.bug_dialog.form.severity_levels.annoying'),
          },
          {
            value: 3,
            text: Translator.message('support.bug_dialog.form.severity_levels.very_annoying'),
          },
          {
            value: 4,
            text: Translator.message('support.bug_dialog.form.severity_levels.usability_issues'),
          },
          {
            value: 5,
            text: Translator.message('support.bug_dialog.form.severity_levels.unusable'),
          },
          {
            value: 10,
            text: Translator.message('support.bug_dialog.form.severity_levels.security'),
          },
        ];
      },
    },
    data() {
      return {
        title: '',
        details: '',
        severity: {
          value: '1',
        },
        reproduce: '',
        message: Translator.message('support.bug_dialog.content'),
        state: 'primary',
      };
    },
    methods: {
      reset() {
        this.title = '';
        this.details = '';
        this.severity = {
          value: '1',
        };
        this.reproduce = '';
        this.message = Translator.message('support.bug_dialog.content');
        this.state = 'primary';
      },
      async submit() {
        const data = {
          title: this.title,
          details: this.details,
          severity: this.severity?.value,
          reproduce: this.reproduce,
        };

        try {
          this.message = Translator.message('support.bug_dialog.sending');
          this.state = 'loading';
          await JinyaRequest.post('/api/support/bug', data);
          this.$emit('close');
        } catch (e) {
          this.message = Translator.message('support.bug_dialog.error');
          this.state = 'error';
        }
      },
    },
  };
</script>

<style lang="scss" scoped>
  .is--bug-dialog {
    margin: 0;
    width: 100%;
  }
</style>
