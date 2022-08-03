<?php

namespace IsotopeKit\Utility;

class Domain
{

	private $domain;
	private $app_ip;

	public function __construct($domain, $app_ip)
	{
		$this->domain = $domain;
		$this->app_ip = $app_ip;
	}

	private function trimUrlProtocol($url) {
		return preg_replace('/((^https?:\/\/)?(www\.)?)|(\/$)/', '', trim($url));
	}

	public function verify()
    {
		try
		{
			$domain = $this->trimUrlProtocol($this->domain);

			$domain_record_exists = false;
			$domain_cloudflare = false;

			$result = dns_get_record($domain, DNS_A);
			
			if(array_key_exists(0, $result))
			{
				if($result[0]['ip'] == $this->app_ip)
				{
					$domain_record_exists = true;
				}

				if($result[0]['ip'] == '104.21.80.199' || $result[0]['ip'] == '172.67.153.72')
				{
					$domain_cloudflare = true;
				}
			}

			if($domain_record_exists == false)
			{
				if($domain_cloudflare == true)
				{
					// return
					// domain managed by cloudflare
					return "managed_by_cloudflare";
				}

				// return
				// domain DNS A Record not pointed to IP
				return "not_pointing_to_ip";
			}

			// check site is SSL secured
			$domain_ssl_secured = false;
			$ssl_check = @fsockopen('ssl://' . $domain, 443, $errno, $errstr, 30);
			$res = !! $ssl_check;
			if ($ssl_check)
			{
				fclose( $ssl_check );
			}
			$domain_ssl_secured = $res;

			if($domain_ssl_secured == false)
			{
				// create apache file
				$content = @"
				<IfModule mod_ssl.c>
				<VirtualHost *:80>
					ServerName ".$domain."
					Redirect permanent / https://".$domain."/
				</VirtualHost>
				<VirtualHost *:443>
						ServerName ".$domain."
						
						ServerAdmin webmaster@localhost
						DocumentRoot /var/www/html

						<Directory /var/www/html>
								Options Indexes FollowSymLinks MultiViews
								AllowOverride All
								Require all granted
						</Directory>

						ErrorLog \${APACHE_LOG_DIR}/error.log
						CustomLog \${APACHE_LOG_DIR}/access.log combined
				</VirtualHost>
				</IfModule>";

				file_put_contents("/etc/apache2/sites-available/".$domain.".conf", $content);
				
				// enable new domain
				exec("sudo a2ensite ".$domain);
				
				// enable certificate
				exec("echo '' | sudo certbot --apache --non-interactive --agree-tos --redirect --register-unsafely-without-email -d ".$domain." 2>&1", $output, $return_var);

				// return
				// SSL enabled;
				return "ssl_enabled";
			}

			// return
			// already secured
			return "already_secured";
		}
		catch(\Exception $ex){
			return "something_went_wrong";
		}
	}
}