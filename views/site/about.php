<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        This is a case-study with the purpose of distributing third-party plugins to different servers on a hosting platform
    </p>
    <p>
        We may assume that there are multiple servers (the single-server scenario is a trivial case) and we are to divide our hosting platform
        to the following domains:
    </p>
    <ul>
        <li><b>database server(s)</b>:
        It makes sense to have a main database, that contains the core features. Depending on the complexity of the schema, it may be feasible
        to create multiple further databases for the core plugins, yet, third-party plugins need to be in some manner separated from the
        core database(s), because they may be unsecure, even despite the most rigorous reviews, hence it makes sense to separate "physically"
        third-party plugin-related database support and, untrusted third-party plugins may access the main features via secure APIs. Naturally,
        we may shard our main or side databases, but conceptually we need the division between core and third-party content</li>
        <li><b>file (and name) server(s)</b>:
        We have a certain amount of storage necessary for the files and we need to allocate the necessary storage. Of course, the downloadable
        files should be secure from malicious or unsafe content, so they may not harm the OS and/or integrity of the servers, but in the case of
        third-party plugins, especially if they were not reviewed, unpacking such plugins entail a certain amount of risk, so, when a user is to
        download them, they are to be notified that the given plugins were not reviewed yet</li>
        <li><b>staging servers</b>:
        These servers are enabling enthusiasts to try their hands with certain available plugins, that are connected to some staging database
        that we do not care about, such servers would allow testers, developers first and foremost to test and try the plugins in order to review
        them and once they are acknowledged to be fairly safe, they may be open for beta testing</li>
        <li><b>operational</b>:
        These application servers would hold third-party plugins that the hosting platform supports so they are readily available for the wider
        audience, who may use them either without limits, or with trial/paid accounts. Since these servers would actually be in use, we
        need to properly separate them by safety-level and, possibly versions; yet, if several copies can be stored on the same server, then
        the given server can host multiple versions/staging environments</li>
    </ul>
    <p>
        Further manners for division include version and resource dependencies as well as overlap between tags and categories, for example Math
        plugins are to gravitate towards each-other rather than scattered
    </p>

</div>
