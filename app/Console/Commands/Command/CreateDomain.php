<?php

namespace App\Console\Commands\Command;

use Illuminate\Console\Command;

class CreateDomain extends Command
{

    protected $signature = 'create:domain {domainName}';

    protected $description = 'Create a domain with all components';

    public function handle()
    {
        $domainName = $this->argument('domainName');
        $domainPath = app_path("Domain/{$domainName}");
        $this->createDomainFolder($domainPath);
        $this->createFolder($domainPath, 'Models');
        $this->createFolder($domainPath, 'Controllers');
        $this->createController($domainPath, 'Controllers', $domainName);
        $this->createFolder($domainPath, 'DTO');
        $this->createDTO($domainPath, 'DTO', $domainName);
        $this->createFolder($domainPath, 'Requests');
        $this->createRequest($domainPath, 'Requests', $domainName);
        $this->createFolder($domainPath, 'Features');
        $this->createFeature($domainPath, 'Features', $domainName);
        $this->createFolder($domainPath, 'Operations');
        $this->createFolder($domainPath, 'Actions');
        $this->createFolder($domainPath, 'ModelData');
        $this->createFolder($domainPath, 'Events');
        $this->createFolder($domainPath, 'Listeners');
        $this->createFolder($domainPath, 'Transformers');
        $this->createTransformer($domainPath, 'Transformers', $domainName);
        $this->createFolder($domainPath, 'Routes');
        $this->createRoute($domainPath, $domainName);
    }

    private function createDomainFolder($domainPath)
    {
        if (!file_exists($domainPath)) {
            mkdir($domainPath, 0777, true);
        }
    }

    private function createFolder($domainPath, $folderName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
    }

    private function createController($domainPath, string $folderName, string $domainName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if (!file_exists("{$folderPath}/{$domainName}Controller.php")) {
            $namespace         = "App\Domain\.$domainName.\Controllers";
            $namespace         = str_replace('.', '', $namespace);
            $controllerContent = "<?php
namespace {$namespace};

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class {$domainName}Controller extends BaseController
{
    public function __construct(Request \$request)
    {
        parent::__construct(\$request);
    }
    public function index()
    {
        // TODO: Implement index method.
    }
}
            ";
            file_put_contents("{$folderPath}/{$domainName}Controller.php", $controllerContent);
        }
    }

    public function createRoute(string $domainPath, string $domainName)
    {
        $domainName = strtolower($domainName);
        if (!file_exists("{$domainPath}/Routes/routes.php")) {
            $routeContent = "
<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '$domainName'], function () {
});";
            file_put_contents("{$domainPath}/Routes/routes.php", $routeContent);
        }
    }

    private function createDTO(string $domainPath, string $folderName, string $domainName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if (!file_exists("{$folderPath}/{$domainName}DTO.php")) {
            $namespace         = "App\Domain\.$domainName.\DTO";
            $namespace         = str_replace('.', '', $namespace);
            $dtoContent = "<?php
namespace {$namespace};

class {$domainName}DTO 
{
    public function __construct()
    {
    }

}
            ";
            file_put_contents("{$folderPath}/{$domainName}DTO.php", $dtoContent);
        }
    }

    private function createRequest(string $domainPath, string $folderName, string $domainName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if (!file_exists("{$folderPath}/{$domainName}Request.php")) {
            $namespace         = "App\Domain\.$domainName.\Requests";
            $namespace         = str_replace('.', '', $namespace);
            $dtoContent = "<?php
namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$domainName}Request extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
        ];
    }
    
    public function messages(): array
    {
        return [
        ];
    }
    
    public function getDTO() 
    {
        
    }

}
            ";
            file_put_contents("{$folderPath}/{$domainName}Request.php", $dtoContent);
        }
    }

    private function createFeature(string $domainPath, string $folderName, string $domainName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if (!file_exists("{$folderPath}/{$domainName}Feature.php")) {
            $namespace         = "App\Domain\.$domainName.\Features";
            $namespace         = str_replace('.', '', $namespace);

            $useDTO      = "App\Domain\.$domainName.\DTO\.$domainName.DTO;";
            $useDTO = str_replace('.', '', $useDTO);

            $dto               = lcfirst($domainName)."DTO";
            $dtoContent = "<?php
namespace {$namespace};

use {$useDTO}

class {$domainName}Feature 
{
    private {$domainName}DTO "."$"."{$dto};
        
    public function __construct()
    {
    }
    public function setDTO({$domainName}DTO "."$"."{$dto})
    {
        "."$"."this->{$dto} = "."$"."{$dto};
    }
    
    public function getDTO(): {$domainName}DTO
    {
        return "."$"."this->{$dto};
    }
    
    public function handle(){
        
    }
}
            ";
            file_put_contents("{$folderPath}/{$domainName}Feature.php", $dtoContent);
        }
    }

    private function createTransformer(string $domainPath, string $folderName, string $domainName)
    {
        $folderPath = "{$domainPath}/{$folderName}";

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if (!file_exists("{$folderPath}/{$domainName}Transformer.php")) {
            $namespace         = "App\Domain\.$domainName.\Transformers";
            $namespace         = str_replace('.', '', $namespace);
            $dtoContent = "<?php
namespace {$namespace};

class {$domainName}Transformer 
{
    public function toArray() {
    
    }

}
            ";
            file_put_contents("{$folderPath}/{$domainName}Transformer.php", $dtoContent);
        }
    }
}
