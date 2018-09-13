$(function () {
    let ServerStore = function (apiUrl, userLogin = "") {
        let store = {
            apiUrl: apiUrl.replace(/\/$/, '') + "/",
            userLogin: userLogin,
            _generateActionUrl: function (action) {
                let url = this.apiUrl + action;
                if (this.userLogin.length > 0) {
                    url += "?user=" + this.userLogin;
                }
                return url;
            },
            _postJson: function (action, data) {
                let store = this;
                return $.ajax({
                    url: this._generateActionUrl(action),
                    type: 'post',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    cache: false,
                    contentType: 'application/json; charset=utf-8',
                    success: function (data) {
                        if (data['status'] != 'ok') {
                            console.log(data);
                            return
                        }
                        store.onReceiveTodos(data['result']['todos']);
                    },
                    fail: function (jqXHR, textStatus, errorThrown) {
                    }
                });
            },

            addTodos: function (todos) {
                this._postJson('add', todos);
            },
            updateTodos: function (todos) {
                this._postJson('update', todos);
            },
            removeTodos: function (todos) {
                this._postJson('remove', todos);
            },

            forceReceiveTodos: function () {
                let store = this;
                $.getJSON(this._generateActionUrl('all'), function (data) {
                    if (data['status'] != 'ok') {
                        console.log(data);
                        return
                    }
                    store.onReceiveTodos(data['result']['todos']);
                })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        console.log([jqXHR, textStatus, errorThrown]);
                    })

            },

            onReceiveTodos: function (todos) {
                console.log(todos)
            },
            onFail: function () {} //Todo
        };
        return store;
    };
    //Todo: добавить LocalStorage для оффлайна

    function App(selector, store, syncInterval = 5000) {
        let ENTER_KEY = 13;
        let ESCAPE_KEY = 27;

        let app = {
            $: $(selector),
            router: null,
            store: store,
            timerId: null,
            syncInterval: syncInterval,

            init: function () {
                let routes = {
                    '/': this.onFilterChange.bind(this),
                    '/:filter': this.onFilterChange.bind(this)
                };
                this.router = new Router(routes);
                this.router.init();
                this.bindEvents();
                this.refreshGeneralControls();
                this.store.forceReceiveTodos();
                let app = this;
                this.timerId = setInterval(this.sync.bind(this), syncInterval);
            },
            sync: function() {
                this.store.forceReceiveTodos();
            },

            refreshGeneralControls: function () {
                let $toggleAllCheckbox = this.$.find('.toggle-all'),
                    $toggleAllLabel = $toggleAllCheckbox.siblings('label'),
                    activeTodosCount = this.$.find('.todo-list li:not(.completed)').length,
                    completedTodosCount = this.$.find('.todo-list li.completed').length,
                    listIsEmpty = (completedTodosCount + activeTodosCount) === 0,
                    listIsCompleted = activeTodosCount === 0;

                $toggleAllCheckbox.prop('checked', listIsCompleted);

                if (listIsEmpty) {
                    $toggleAllLabel.css('display', 'none');
                    this.$.find('.main').hide();
                    this.$.find('.footer').hide();
                } else {
                    $toggleAllLabel.css('display', 'block');
                    this.$.find('.main').show();
                    this.$.find('.footer').show();
                }

                if (completedTodosCount !== 0) {
                    this.$.find('.clear-completed').show();
                } else {
                    this.$.find('.clear-completed').hide();
                }

                this.$.find('.todo-count').html(
                    '<strong>' + activeTodosCount + '</strong> item' + (activeTodosCount === 1 ? '' : 's') + ' left'
                );
                this.onFilterChange(this.router.getRoute(0));
            },

            compileTodo: function (id, title, isCompletedItem) {
                let template =
                    '<li data-id="' + id + '"' + (isCompletedItem ? 'class="completed"' : '') + '>' +
                    '  <div class="view">' +
                    '    <input class="toggle" type="checkbox" ' + (isCompletedItem ? 'checked' : '') + '>' +
                    '    <label>' + title + '</label>' +
                    '    <button class="destroy"></button>' +
                    '  </div>' +
                    '  <input class="edit">' +
                    '</li>';

                return template;
            },

            renderTodos: function (todos) {
                let todosHtml = '';
                for (let i = 0; i < todos.length; i++) {
                    todosHtml += this.compileTodo(todos[i]['id'], todos[i]['title'], todos[i]['is_completed']);
                }
                this.$.find('.todo-list').html(todosHtml);
            },

            getTodos: function () {
                let app = this;
                return this.$.find('.todo-list li').map(function () {
                    let $todo = $(this);
                    return app.todoToArray($todo);
                });
            },
            todoToArray: function ($todo) {
                $todo = $($todo);
                return {
                    id: $todo.data('id'),
                    title: $todo.find('label').text(),
                    is_completed: $todo.hasClass('completed')
                };
            },

            bindEvents: function () {
                this.store.onReceiveTodos = this.onNewTodosReceive.bind(this);
                this.$
                    .on('keydown', '.new-todo', this.onNewTodo.bind(this))
                    .on('click', '.clear-completed', this.onRemoveCompleted.bind(this))
                    .on('change', '.toggle-all', this.onToggleAll.bind(this));

                this.$.find('.todo-list')
                    .on('dblclick', 'label', this.onEditStart.bind(this))
                    .on('blur', '.edit', this.onEditBlur.bind(this))
                    .on('keydown', '.edit', this.onEditKeydown.bind(this))
                    .on('change', '.toggle', this.onToggleTodo.bind(this))
                    .on('click', '.destroy', this.onRemoveTodo.bind(this));
            },
            /**
             * Events handlers:
             */
            onNewTodosReceive: function (todos) {
                // let oldState = this.getTodos();
                // if (oldState != todos) {//Todo: Добавить проверку на изменение
                this.renderTodos(todos);
                this.refreshGeneralControls();
                // }
            },
            onFilterChange: function (filter) {
                switch (filter) {
                    case 'active': {
                        this.$.find('.filters a').removeClass('selected');
                        this.$.find('.filters a[href="#/active"]').addClass('selected');
                        this.$.find('.todo-list li').hide();
                        this.$.find('.todo-list li:not(.completed)').show();
                        break;
                    }
                    case 'completed': {
                        this.$.find('.filters a').removeClass('selected');
                        this.$.find('.filters a[href="#/completed"]').addClass('selected');
                        this.$.find('.todo-list li').hide();
                        this.$.find('.todo-list li.completed').show();
                        break;
                    }
                    default: {
                        this.$.find('.filters a').removeClass('selected');
                        this.$.find('.filters a[href="#/"]').addClass('selected');
                        this.$.find('.todo-list li').show();
                        break;
                    }
                }
            },

            onNewTodo: function (event) {
                if (!(event.which == ENTER_KEY || event.keyCode == ENTER_KEY)) {
                    return;
                }
                let $textInputOfNewTodo = $(event.target);
                let textVal = $textInputOfNewTodo.val().trim();
                if (!textVal) {
                    return;
                }

                let $todo = $(this.compileTodo(-1, textVal, false));
                this.$.find('.todo-list').append($todo);
                $textInputOfNewTodo.val('');
                this.store.addTodos([this.todoToArray($todo)]);

                this.refreshGeneralControls();
                event.preventDefault();
            },


            onEditStart: function (event) {
                $(event.target).closest('li')
                    .toggleClass('editing')
                    .find('.edit').focus().val(event.target.innerText);
            },

            onEditKeydown: function (event) {
                if (event.which == ENTER_KEY || event.keyCode == ENTER_KEY) {
                    event.target.blur();
                } else if (event.which == ESCAPE_KEY || event.keyCode == ESCAPE_KEY) {
                    let oldValue = $(event.target).closest('li').find('label').text();
                    $(event.target).val(oldValue);
                    event.target.blur();
                }
            },

            onEditBlur: function (event) {
                let $editInput = $(event.target),
                    $todo = $editInput.closest('li'),
                    newValue = $editInput.val().trim();

                if (!newValue) {
                    this.store.removeTodos([this.todoToArray($todo)]);
                    $todo.remove();
                } else {
                    $todo.find('label').text(newValue);
                    $todo.toggleClass('editing');
                    $editInput.val('');
                    this.store.updateTodos([this.todoToArray($todo)]);
                }

                this.refreshGeneralControls();
            },

            onToggleTodo: function (event) {
                let $todo = $(event.target).closest('li');
                $todo.toggleClass('completed');
                this.store.updateTodos([this.todoToArray($todo)]);
                this.refreshGeneralControls();
            },

            onToggleAll: function () {
                let isListCompleted = this.$.find('.todo-list li:not(.completed)').length === 0;
                let $checkBoxNeedToggle = isListCompleted ? this.$.find('.toggle') : this.$.find('li:not(.completed) .toggle');

                $checkBoxNeedToggle
                    .prop('checked', !isListCompleted);
                let $todos = $checkBoxNeedToggle.closest('li');
                $todos.toggleClass('completed');
                this.store.updateTodos($.map($todos, this.todoToArray));
                this.refreshGeneralControls();
            },

            onRemoveTodo: function (event) {
                let $todo = $(event.target).closest('li');
                this.store.removeTodos([this.todoToArray($todo)]);
                $todo.remove();
                this.refreshGeneralControls();
            },

            onRemoveCompleted: function (event) {
                let $todos = this.$.find('li.completed');
                this.store.removeTodos($.map($todos, this.todoToArray));
                $todos.remove();
                this.refreshGeneralControls();
            },
        };
        return app;
    }

    /**
     * Start app
     */
    let store = new ServerStore("/api/todos")
    let app = new App('.todoapp', store, 4000);
    app.init();
});
