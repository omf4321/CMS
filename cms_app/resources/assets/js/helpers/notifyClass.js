export default {
    success(selector = ".user_nav", duration = 3000) {
        const el = document.querySelector(selector);
        if (el) {
            el.classList.add("successful");
            setTimeout(() => el.classList.remove("successful"), duration);
        }
    },

    denied(selector = ".user_nav", duration = 3000) {
        const el = document.querySelector(selector);
        if (el) {
            el.classList.add("denied");
            setTimeout(() => el.classList.remove("denied"), duration);
        }
    }
};

