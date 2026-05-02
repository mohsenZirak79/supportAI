/**
 * باید قبل از هر اسکریپت ادمینی که به Bootstrap تکیه دارد لود شود (soft-ui-dashboard و غیره).
 * با hoisting در ESM، بدنهٔ admin.js قبل از importهایش اجرا نمی‌شود؛ پس window.bootstrap اینجا ست می‌شود.
 */
import * as bootstrap from 'bootstrap';

if (typeof window !== 'undefined') {
    window.bootstrap = bootstrap;
}

export { bootstrap };
