<template>
  <jinya-editor>
    <jinya-message :message="message" :state="state"/>
    <jinya-form :enable="enable" @back="cancel"
                @submit="change" cancel-label="my_jinya.account.password.cancel"
                save-label="my_jinya.account.password.save">
      <jinya-input :enable="enable" :required="true" label="my_jinya.account.password.old_password"
                   type="password" v-model="oldPassword"/>
      <jinya-input :enable="enable" :required="true" label="my_jinya.account.password.new_password"
                   type="password" v-model="newPassword"/>
      <jinya-input :enable="enable" :required="true" label="my_jinya.account.password.new_password_repeat"
                   type="password" v-model="newPasswordRepeat"/>
    </jinya-form>
  </jinya-editor>
</template>

<script>
  import JinyaForm from '@/framework/Markup/Form/Form';
  import JinyaInput from '@/framework/Markup/Form/Input';
  import JinyaEditor from '@/framework/Markup/Form/Editor';
  import JinyaMessage from '@/framework/Markup/Validation/Message';
  import Translator from '@/framework/i18n/Translator';
  import JinyaRequest from '@/framework/Ajax/JinyaRequest';
  import Routes from '@/router/Routes';
  import Timing from '@/framework/Utils/Timing';

  export default {
    name: 'Password',
    components: {
      JinyaMessage,
      JinyaEditor,
      JinyaInput,
      JinyaForm,
    },
    data() {
      return {
        state: '',
        message: '',
        oldPassword: '',
        newPassword: '',
        newPasswordRepeat: '',
        enable: true,
      };
    },
    methods: {
      cancel() {
        this.$router.push(Routes.MyJinya.Account.Profile);
      },
      async change() {
        if (this.newPassword !== this.newPasswordRepeat) {
          this.state = 'error';
          this.message = Translator.validator('my_jinya.account.password.passwords_dont_match');
        } else {
          this.enable = false;
          try {
            this.state = 'loading';
            this.message = Translator.message('my_jinya.account.password.asking_for_token');
            const changeData = await JinyaRequest.put('/api/account/password', {
              old_password: this.oldPassword,
            });

            this.message = Translator.message('my_jinya.account.password.changing');
            await JinyaRequest.put(changeData.url, {
              token: changeData.token,
              password: this.newPassword,
            });

            this.message = Translator.message('my_jinya.account.password.changed');

            await Timing.wait();
            this.$router.push(Routes.MyJinya.Account.Profile);
          } catch (e) {
            this.enable = true;
            this.message = Translator.validator(`my_jinya.account.password.${e.message}`);
            this.state = 'error';
          }
        }
      },
    },
  };
</script>
