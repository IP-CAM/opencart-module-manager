<?php echo $header ?>

<?php require DIR_APPLICATION . '/view/template/teil/teil-head.php' ?>


<!-- Entry application -->
<div ng-app="teil" id="content">
	<div id="self-updater" ng-controller="SelfController">
		<div ng-cloak class="__has-progress bs-callout bs-callout-warning" ng-show="showUpdate" data-progress="{{ progress }}">
			<h4>{{ 'NEW_VERSION_TEXT' | translate }}</h4>

			<button class="btn__blue ng-pristine ng-valid" ng-click="update()" ng-disabled="disableButton">{{ 'UPDATE_TEXT' | translate }}</button>

			<div class="over"></div>
		</div>
	</div><!-- end #self-updater -->

	<div ng-controller="CommonController" ng-class="{'loading': loading}" class="has-loading" id="module-list-container">
		<div id="loading" ng-class="{'show-loading popin': loading}" class="clock-loading">
			<img src="/admin/view/image/teil/loading_clock.png" class="loading-icon">
		</div>

		<div ng-cloak class="boxed-block" ng-class="{'show-me': !totalInstalledModules}" class="ng-hide">
			<span class="glyphicon glyphicon-bullhorn"></span>
			<h5 class="__h2">{{ 'YOU_HAVE_0_MODULES' | translate }}</h5>
			<div class="info__small">{{ 'YOU_HAVE_0_MODULES_DESC' | translate }}</div>
		</div><!-- end .boxed-block -->

		<!-- List of already installed modules -->
		<div ng-cloak class="installed-modules" ng-show="totalInstalledModules">
			<h3 class="__h2">{{ 'INSTALLED_MODULES_TEXT' | translate }}</h3>
			<div class="info__small">{{ 'INSTALLED_MODULES_DESC' | translate }}</div>

			<ul class="modules__list">
				<module-thumb ng-repeat="module in modules" ng-class="{'show-module popin': module.installed}"></module-thumb>
			</ul><!-- end ,.modules__list -->
		</div><!-- end .installed-modules -->

		<!-- List of all avalible modules -->
		<h3 class="__h2">{{ 'NOT_INSTALLED_MODULES_TEXT' | translate }}</h3>
		<div class="info__small">{{ 'NOT_INSTALLED_MODULES_DESC' | translate }}</div>

		<ul class="modules__list">
			<module-thumb ng-repeat="module in modules" class="show-module"></module-thumb>
		</ul><!-- end ,.modules__list -->

	</div><!-- end #content -->
</div><!-- end NG-APP -->


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>

<?php echo $footer ?>