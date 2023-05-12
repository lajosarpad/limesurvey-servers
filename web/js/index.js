window.addEventListener('load', function() {
    for (let func of registeredFunctions) {
        func();
    }
    let data = {
        servers: [],
        plugins: []
    };
    function removeServer(context) {
        let serverContainer = context.parentNode.parentNode;
        let mainContainer = serverContainer.parentNode;
        let index = [...mainContainer.children].indexOf(serverContainer);
        data.servers.splice(index, 1);
        serverContainer.remove();
    }
    function getServer(json) {
        return `
            <div class="border p-3 mb-3">
                <p>Server Type: ${json.OS || "-"}</p>
                <p>Storage Size: ${json.storageSize}</p>
                <p><input type="button" value="DELETE" class="remove-server btn btn-primary"></p>
            </div>
        `;
    }
    function displayServer(json) {
        let context = document.getElementById('servers');
        context.insertAdjacentHTML('beforeend', getServer(json));
        for (let uninitialized of context.querySelectorAll('input.remove-server:not(.initialized)')) {
            uninitialized.addEventListener('click', function() {
                removeServer(uninitialized);
            });
            uninitialized.classList.add('initialized');
        }
    }
    function displayServers(servers) {
        let context = document.getElementById('servers');
        let template = '';
        for (let server of servers) template += getServer(server);
        context.insertAdjacentHTML('beforeend', template);
        for (let uninitialized of context.querySelectorAll('input.remove-server:not(.initialized)')) {
            uninitialized.addEventListener('click', function() {
                removeServer(uninitialized);
            })
            uninitialized.classList.add('initialized');
        }
        data.servers = servers;
    }
    function getPlugin(json) {
        return `
            <div class="border p-3 mb-3">
                <p>Plugin Name: ${json.name}</p>
                <p>Supported versions: ${json.versions.join(',')}</p>
                <p>Supported operating systems: ${json.operatingSystems.join(',')}</p>
                <p>Size: ${json.size}</p>
                <p><input type="button" value="DELETE" class="remove-plugin btn btn-primary"></p>
            </div>
        `;
    }
    function displayPlugin(json) {
        let context = document.getElementById('plugins');
        context.insertAdjacentHTML('beforeend', getPlugin(json));
        for (let uninitialized of context.querySelectorAll('input.remove-plugin:not(.initialized)')) {
            uninitialized.addEventListener('click', function() {
                removePlugin(uninitialized);
            });
            uninitialized.classList.add('initialized');
        }
    }
    function displayPlugins(plugins) {
        let context = document.getElementById('plugins');
        let template = '';
        for (let plugin of plugins) template += getPlugin(plugin);
        context.insertAdjacentHTML('beforeend', template);
        for (let uninitialized of context.querySelectorAll('input.remove-plugin:not(.initialized)')) {
            uninitialized.addEventListener('click', function() {
                removePlugin(uninitialized);
            });
            uninitialized.classList.add('initialized');
        }
        data.plugins = plugins;
    }
    function removePlugin(context) {
        let pluginContainer = context.parentNode.parentNode;
        let mainContainer = pluginContainer.parentNode;
        let index = [...mainContainer.children].indexOf(pluginContainer);
        data.plugins.splice(index, 1);
        pluginContainer.remove();
    }
    document.getElementById('add-server').addEventListener('click', function() {
        let context = document.getElementById('server-form');
        let storageSize = parseInt(context.querySelector('#storage-size').value);
        if (!(storageSize > 0)) {
            alert("Storage Size must be positive");
            return;
        }
        let newServer = {
            OS: context.querySelector('#OS').value,
            storageSize: storageSize
        };
        displayServer(newServer);
        data.servers.push(newServer);
    });

    document.getElementById('add-plugin').addEventListener('click', function() {
        let context = document.getElementById('plugin-form');
        let name = context.querySelector("#plugin-name").value;
        if (!name) {
            alert("The plugin needs a name");
            return;
        }
        let versions = [...context.querySelectorAll("#plugin-versions input[type=checkbox]")].filter(item => item.checked).map(item => item.value);
        if (!versions.length) {
            alert("Your plugin needs to be compatible with at least one supported version");
            return;
        }
        let operatingSystems = [...context.querySelectorAll("#plugin-os input[type=checkbox]")].filter(item => item.checked).map(item => item.value);
        if (!operatingSystems.length) {
            alert("Your plugin needs to be compatible with at least one supported operating system");
            return;
        }
        let size = parseInt(context.querySelector("#plugin-size").value);
        if (!(size > 0)) {
            alert("Your plugin needs to have a strictly positive size");
            return;
        }
        let newPlugin = {
            name,
            versions,
            operatingSystems,
            size
        };
        displayPlugin(newPlugin);
        data.plugins.push(newPlugin);
    });

    document.getElementById("randomize").addEventListener('click', function() {
        sendRequest('POST', 'index.php?r=site/generate-test-data', function() {
            if (this.readyState === 4) {
                data = {
                    servers: [],
                    plugins: []
                };
                document.getElementById('servers').innerHTML = "";
                document.getElementById('plugins').innerHTML = "";
                let json = JSON.parse(this.responseText);
                displayServers(json.servers);
                displayPlugins(json.plugins);
            }
        }, true, `servers=${document.getElementById('serverCount').value}&plugins=${document.getElementById('pluginCount').value}`);
    });

    function getServerInformation(json) {
        let template = "";
        let index = 0;
        for (let server of json) {
            let pluginTemplate = '';
            let size = 0;
            let set = new Set();
            for (let plugin of server.plugins ?? []) {
                size += plugin.size;
                for (let v of plugin.versions) set.add(v);
                pluginTemplate += `
                    <ul>
                        <li><b>Name:</b> ${plugin.name}</li>
                        <li><b>Versions:</b> ${plugin.versions}</li>
                        <li><b>Operating systems:</b> ${plugin.operatingSystems}</li>
                        <li><b>size:</b> ${plugin.size}</li>
                    </ul><hr>
                `;
            }
            template += `
                <h2>${++index}</h2>
                <p><b>OS:</b> ${server.OS}</p>
                <p><b>storageSize:</b> ${server.storageSize}</p>
                <p><b>Plugin size usage: </b>${size}</p>
                <p><b>Necessary version support: </b>${[...set]}</p>
                ${pluginTemplate}
            `;
        }
        return template;
    }

    this.document.getElementById('distribute-servers').addEventListener('click', function() {
        sendRequest('POST', 'index.php?r=site/distribute-servers', function() {
            if (this.readyState === 4) {
                let input = document.getElementById('input');
                let output = document.getElementById('output');
                input.style.display = 'none';
                output.innerHTML = `<button id="back" class="btn btn-primary">BACK</button><br>` + getServerInformation(JSON.parse(this.responseText));
                document.getElementById('back').addEventListener('click', function() {
                    input.style.display = 'block';
                    output.innerHTML = '';
                })
            }
        }, true, "data=" + btoa(JSON.stringify(data)));
    });
});