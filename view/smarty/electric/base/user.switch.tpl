{extends 'base/index'}

{block name="content_title"}
    <div class="page-header">
        <h1>{'title.user.switch'|translate}</h1>
    </div>
{/block}

{block name="content" append}
    <p>{"label.user.switch.description"|translate}</p>

    {include file="base/form.prototype"}

    <form id="{$form->getId()}" class="form form--selectize" action="{$app.url.request}" method="POST" role="form">
        <div class="form__group">
            <div class="grid">
                <div class="grid__12 grid--bp-sml__8">
                    {call formRows form=$form}
                </div>
            </div>

            {call formActions referer=$referer submit="button.switch"}
        </div>
    </form>
{/block}

{block name="scripts" append}
    {$script = 'js/form.js'}
    {if !isset($app.javascripts[$script])}
        <script src="{$app.url.base}/electric/js/form.js"></script>
    {/if}
{/block}
