const API = {
    get: (endpoint, data = {}) => {
        return $.ajax({
            url: `/api/${endpoint}`,
            method: 'GET',
            data: data,
            dataType: 'json'
        });
    },

    post: (endpoint, data = {}) => {
        return $.ajax({
            url: `/api/${endpoint}`,
            method: 'POST',
            data: data,
            dataType: 'json'
        });
    },

    logout: () => {
        return API.post('auth/logout')
        .then(response => {
            window.FlashMessage.success(response.message, {progress: true, timeout: 1000});
            setTimeout(() => {
                window.location.reload();
            }, 1100);
        })
    }
}
