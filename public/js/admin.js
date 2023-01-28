const showError = function (title, body) {
        $(document).Toasts('create', {
            class: 'bg-danger',
            title: title,
            body: body,
        })
    },
    clearValidation = function ($form) {
        $('input', $form)
            .removeClass('is-invalid')
            .siblings('.invalid-feedback').remove()
        ;
    },

    createErrorHandler = function ($formInputsContainer) {
        return function (error) {
            if (error.response) {
                // The request was made and the server responded with a status code
                // that falls out of the range of 2xx
                if (error.response.status === 422) {
                    if (error.response.data && error.response.data.errors) {
                        const fields = $('input, select', $formInputsContainer);

                        for (let fname in error.response.data.errors) {
                            for (let i = 0; i < error.response.data.errors[fname].length; i++) {
                                fields.filter('[name="' + fname + '"]').addClass('is-invalid').parent().append(
                                    $('<span/>', {
                                        class: 'invalid-feedback',
                                        text: error.response.data.errors[fname][i]
                                    })
                                )
                            }
                        }

                        return;
                    }
                }

                showError('HTTP Error ' + error.response.status, error.response.statusText);
            }
            else if (error.request) {
                // The request was made but no response was received
                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                // http.ClientRequest in node.js
                // console.log(error.request);
                showError('The request was made but no response was received', error.request);
            }
            else {
                // Something happened in setting up the request that triggered an Error
                // console.log('Error', error.message);
                showError('Error', error.message);
            }
        }
    }

function callApi(method, url, data) {
    return axios({
        url: url,
        method: method,
        data: data
    });
}