/*
 * File: assets\controllers\pswsetup_controller.js
 * Author: Peter Nagy <peter@antronin.consulting>
 * -----
 */
/* stimulusFetch: 'lazy' */
import { Controller } from "@hotwired/stimulus"
export default class extends Controller {
    static values = { setup: String }
    static targets = ['psw_setup_display'];

    connect() {
        this.setup = JSON.parse(atob(this.setupValue));
        if (this.setup.minLowercase) {
            this.minLowercase = parseInt(this.setup.minLowercase);
            this.lowercasePattern = new RegExp('[' + this.setup.lowercasePattern + ']{' + this.minLowercase + ',}', 'u');
        }
        if (this.setup.minUppercase) {
            this.minUppercase = parseInt(this.setup.minUppercase);
            this.uppercasePattern = new RegExp('[' + this.setup.uppercasePattern + ']{' + this.minUppercase + ',}', 'u');
        }
        if (this.setup.minNumber) {
            this.minNumber = parseInt(this.setup.minNumber);
            this.numberPattern = new RegExp('[' + this.setup.numberPattern + ']{' + this.minNumber + ',}', 'u');
        }
        if (this.setup.minNumber) {
            this.minSpecial = parseInt(this.setup.minSpecial);
            this.specialPattern = new RegExp('[' + this.setup.specialsPattern + ']{' + this.minSpecial + ',}', 'u');
        }
        this.input = this.element.getElementsByTagName('input')[0];

    }

    calc() {
        console.log(this.input.value);
        console.log('minLength: ', this.input.value.length >= this.setup.minLength);
        console.log('maxLength: ', this.input.value.length <= this.setup.maxLength);
        console.log('lowercase: ', this.lowercasePattern.test(this.input.value));
        console.log('uppercase: ', this.uppercasePattern.test(this.input.value));
        console.log('number: ', this.numberPattern.test(this.input.value));
        console.log('special: ', this.specialPattern.test(this.input.value));
    }
}
