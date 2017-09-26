<?xml version="1.0" encoding="utf-8"?>
<configdata>
<!--BeginSezRule--><rule>
        <source>{source_path}</source>
		<!--BeginSezRuleUserAgent--><useragent>
			<!--BeginSezRuleUserAgentTablet--><tablet>{tablet}</tablet><!--EndSezRuleUserAgentTablet-->
			<!--BeginSezRuleUserAgentPhone--><phone>{phone}</phone><!--EndSezRuleUserAgentPhone-->
			<!--BeginSezRuleUserAgentPlatform--><platform>{platform}</platform><!--EndSezRuleUserAgentPlatform-->
			<!--BeginSezRuleUserAgentBrowser--><browser>{browser}</browser><!--EndSezRuleUserAgentBrowser-->
			<!--BeginSezRuleUserAgentUtilities--><utilities>{utilities}</utilities><!--EndSezRuleUserAgentUtilities-->
		</useragent><!--EndSezRuleUserAgent-->
        <query>{source_query}</query>
        <!--BeginSezRevert--><revert /><!--EndSezRevert-->
        <destination>
            <header>{header}</header>
            <!--BeginSezHost--><host>{host}</host><!--EndSezHost-->
            <location>{user_path}</location>
        </destination>
    </rule>
    <!--EndSezRule-->
</configdata>