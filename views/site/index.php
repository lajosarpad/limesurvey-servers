<script>
    let OS = <?php echo $os; ?>;
    let versions = <?php echo $versions; ?>;
    registerFunction(function() {
        let operatingSystems = `<option value="">OS</option>` + 
                               OS.map(item => `<option value="${item}">${item}</option>`).join('');
        document.getElementById('OS').innerHTML = operatingSystems;
        operatingSystems =
        '<ul>' +
        OS.filter(item => item).map(
            item => `
                <li>
                    <label for="plugin-os-${item}">${item}
                        <input type="checkbox" id="plugin-os-${item}" value="${item}">
                    </label>
                </li>
            `
        ).join('');
        '</ul>';
        document.getElementById('plugin-os').innerHTML = operatingSystems;
    });
    registerFunction(function() {
        document.getElementById('plugin-versions').innerHTML = 
        '<ul>' +
        versions.map(
            item => `
                <li>
                    <label for="plugin-version-${item}">${item}
                        <input type="checkbox" id="plugin-version-${item}" value="${item}">
                    </label>
                </li>
            `
        ).join('') +
        '</ul>';
    });
    registerFunction(function() {
        let template = '';
        for (let i = 1; i <= 25; i++) {
            template += '<option value="' + i + '">' + i + '</option>';
        }
        document.getElementById('serverCount').innerHTML = template;
        for (let i = 26; i <= 150; i++) {
            template += '<option value="' + i + '">' + i + '</option>';
        }
        document.getElementById('pluginCount').innerHTML = template;
    });
</script>
<div id="input">
    <button id="distribute-servers" class="btn btn-primary">DISTRIBUTE SERVERS</button>
    <br><br>
    <label for="serverCount">Server Count
        <select id="serverCount" class="form-select mb-3">
        </select>
    </label>
    <label for="pluginCount">Plugin Count
        <select id="pluginCount" class="form-select">
        </select>
    </label>
    <button id="randomize" class="btn btn-primary">RANDOMIZE SET</button>
    <div class="row">
        <div class="col-6">
            <div id="server-form" class="border p-3 mb-3">
                <p><label for="OS">OS <select id="OS" class="form-select"></select></label></p>
                <p><label for="storage-size">Storage Size<input id="storage-size" type="number" class="form-control" value="0"></label></p>
                <p><input id="add-server" type="button" class="btn btn-primary" value="Add"></p>
            </div>
        </div>
        <div id="servers" class="col-6"></div>
        </div>
    <div class="row">
        <div class="col-6">
            <div id="plugin-form" class="border p-3">
                <p><label for="plugin-name">Name&nbsp;<input type="text" id="plugin-name" class="form-control"></label></p>
                <p><div id="plugin-versions"></div></p>
                <p><div id="plugin-os"></div></p>
                <p>Size&nbsp;<label for="plugin-size"><input id="plugin-size" class="form-control" type="number"></label></p>
                <p><input id="add-plugin" type="button" class="btn btn-primary" value="Add"></p>
            </div>
        </div>
        <div id="plugins" class="col-6"></div>
    </div>
</div>
<div id="output"></div>
<?php

$this->registerJsFile('@web/js/index.js');
$this->registerJsFile('@web/js/helpers/request.js');
