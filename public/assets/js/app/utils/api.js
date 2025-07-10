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
    }
}
