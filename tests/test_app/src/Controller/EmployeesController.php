<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiForm;
use SwaggerBake\Lib\Attribute\OpenApiHeader;
use SwaggerBake\Lib\Attribute\OpenApiPaginator;
use SwaggerBake\Lib\Attribute\OpenApiPath;
use SwaggerBake\Lib\Attribute\OpenApiOperation;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Attribute\OpenApiRequestBody;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;
use SwaggerBake\Lib\Extension\CakeSearch\Attribute\OpenApiSearch;
use SwaggerBakeTest\App\Dto\CustomResponseSchema;
use SwaggerBakeTest\App\Dto\CustomResponseSchemaPublic;
use SwaggerBakeTest\App\Dto\EmployeeDataRequest;
use SwaggerBakeTest\App\Dto\EmployeeDataRequestPublicSchema;
use SwaggerBakeTest\App\Form\TestForm;

#[OpenApiPath(
    description: 'description here',
    summary: 'summary here'
)]
class EmployeesController extends AppController
{
    public ?string $defaultTable = 'Employees';

    public function initialize() : void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['search', 'search2'],
        ]);
    }

    /**
     * Gets Employees
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    #[OpenApiOperation(tagNames: ['Employees','CustomTag'])]
    public function index()
    {
        $employees = $this->paginate($this->Employees);
        $this->set(compact('employees'));
        $this->viewBuilder()->setOption('serialize', ['employees']);
    }

    /**
     * View method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $employee = $this->Employees->get($id, [
            'contain' => ['DepartmentEmployees', 'EmployeeSalaries', 'EmployeeTitles'],
        ]);

        $this->set('employee', $employee);
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $employee = $this->Employees->newEmptyEntity();
        if ($this->request->is('post')) {
            $employee = $this->Employees->patchEntity($employee, $this->request->getData());
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $employee = $this->Employees->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $employee = $this->Employees->patchEntity($employee, $this->request->getData());
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->viewBuilder()->setOption('serialize', ['employee']);
    }

    /**
     * Delete employee
     *
     * @param $id
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id): void
    {

    }

    /**
     * custom-get summary
     *
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \Cake\Http\Exception\UnauthorizedException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @throws \Exception
     */
    #[OpenApiSecurity(name: 'BearerAuth')]
    #[OpenApiQueryParam(name: 'queryParamName', type: "string", isRequired: false)]
    #[OpenApiHeader(name: 'X-HEAD-ATTRIBUTE', type: 'string', isRequired: true)]
    #[OpenApiPaginator]
    public function customGet(): void
    {

    }

    #[OpenApiRequestBody(description: "Hello", ignoreCakeSchema: true)]
    #[OpenApiForm(name: "fieldName", type: "string", isRequired: false)]
    public function customPost(): void
    {

    }

    #[OpenApiResponse(ref: '#/components/schemas/Pet')]
    #[OpenApiResponse(statusCode: '404', description: "new statusCode")]
    #[OpenApiResponse(statusCode: '5XX', description: "status code range")]
    public function customResponseRef(): void
    {

    }

    #[OpenApiResponse(schema: CustomResponseSchema::class)]
    public function customResponseSchema(): void
    {

    }

    #[OpenApiResponse(schema: CustomResponseSchemaPublic::class)]
    public function customResponseSchemaPublic(): void
    {

    }

    #[OpenApiResponse(schemaType: 'array', schema: CustomResponseSchemaPublic::class)]
    public function customResponseSchemaPublicArray(): void
    {

    }

    #[OpenApiDto(class: EmployeeDataRequestPublicSchema::class)]
    public function dtoPublic(): void
    {

    }


    /**
     * @deprecated This can be updated to just use alias in v3.0.0
     */
    #[OpenApiSearch(alias: 'Employees')]
    public function search(): void
    {
        $query = $this->Employees
            ->find('search', [
                'search' => $this->request->getQueryParams(),
                'collection' => 'default'
            ]);
        $employees = $this->paginate($query);

        $this->set(compact('employees'));
        $this->viewBuilder()->setOption('serialize', ['employees']);
    }

    public function noResponsesDefined(): void
    {
        $response = 'nokay';
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['response']);
    }

    /**
     * @see OpenApiDtoTest::test_openapi_dto_query()
     */
    #[OpenApiDto(class: EmployeeDataRequest::class)]
    public function dtoQuery(): void
    {

    }

    /**
     * @see OpenApiDto::test_openapi_dto_post()
     */
    #[OpenApiDto(class: EmployeeDataRequest::class)]
    public function dtoPost(): void
    {

    }

    /**
     * @see OpenApiDtoTest::test_openapi_dto_modelless_post_form()
     */
    #[OpenApiDto(class: TestForm::class)]
    public function modellessFormPost()
    {

    }

    /**
     * @see OpenApiDtoTest::test_openapi_dto_modelless_get_form()
     */
    #[OpenApiDto(class: TestForm::class)]
    public function modellessFormGet()
    {

    }
}
