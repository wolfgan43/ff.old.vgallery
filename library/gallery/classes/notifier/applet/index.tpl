<div class="noty-box">
    <div class="noty-background" style="position:fixed;bottom:0;top:0;left:0;right:0;display:none;"></div>
    <div class="dropdown-container" >
        <div class="notifications dropdown dd-trigger" id="bell" >
            <span class="count animated ng-binding fadeIn" id="notifications-count" ></span>
            <span class="bell-icon-svg">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px"
                     height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve">
<g id="Outline_Icons_1_">
	<g id="Outline_Icons">
		<g>
			<path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M20.5,16.5V11
				c0-3.651-2.309-6.756-5.541-7.959C14.738,1.603,13.5,0.5,12,0.5S9.258,1.603,9.037,3.041C5.805,4.244,3.5,7.349,3.5,11v5.5
				c0,1.657-1.344,3-3,3h23C21.841,19.5,20.5,18.157,20.5,16.5z"/>
			<path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M15,19.5
				c0,1.657-1.344,3-3,3c-1.659,0-3-1.343-3-3"/>
		</g>
	</g>
	<g id="New_icons_1_">
	</g>
</g>
<g id="Invisible_Shape">
	<rect fill="none" width="24" height="24"/>
</g>
</svg></span>
        </div>
        <div class="dropdown-menu animated fadeOutUp" id="notification-dropdown">
            <div class="dropdown-header">
                <span class="triangle"></span>
                <span class="heading">{notifier_title}</span>
                <span class="count ng-binding" id="dd-notifications-count"></span>
            </div>
            <div class="notification-empty hidden">
                {notifier_not_found}
            </div>
            <div class="dropdown-body" id="notification-handle" >

            </div>
        </div>
    </div>
</div>