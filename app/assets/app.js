import './bootstrap.js';
import { CountUp } from 'countup.js';

function animateBalance(){
    const balanceEl = document.getElementById('balance');
    if (!balanceEl) return;

    const finalValue = parseInt(balanceEl.dataset.value, 10);

    const countUp = new CountUp('balance', finalValue, {
        duration: 2,
        separator: ' ',
        suffix: ' FCFA'
    });

    if (!countUp.error) {
        countUp.start();
    } else {
        console.error(countUp.error);
    }
}

document.addEventListener('DOMContentLoaded', animateBalance);
document.addEventListener('turbo:load', animateBalance);


/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
