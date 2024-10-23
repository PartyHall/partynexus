<?php

namespace App\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

#[AsController]
class UploadPictureController extends AbstractController
{
    /**
     * Why though?
     *
     * The reason is API Platform is quite badly documented on this,
     * their documentation on file upload doesn't work and does not
     * provide an explanation on how to make it properly.
     *
     * While I do understand exactly why this is happening,
     * I have no clue on how to do it properly.
     *
     * So in the meantime, let's do it the Symfony way
     *
     * Well symfony-ish I don't plan on installing symfony/form
     * or doing a well-thought endpoint for now as this is
     * a temporary fix
     *
     * Oh wow now that I made this method I see that its really ugly
     * don't look at it too much
     *
     * @see https://api-platform.com/docs/core/file-upload/#uploading-to-an-existing-resource-with-its-fields
     * @see https://github.com/api-platform/core/issues/4921
     */

    public function __construct(
        private readonly Security               $security,
        private readonly IriConverterInterface  $iriConverter,
        private readonly EntityManagerInterface $emi,
        private readonly PictureRepository      $pictureRepository,
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface    $normalizer,
        #[Autowire(env: 'PICTURE_LOCATIONS')]
        private readonly string                 $basePath,
    )
    {
    }

    #[Route(
        '/api/pictures',
        name: 'appliance_upload_picture',
        methods: ['POST'],
    )]
    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_APPLIANCE')) {
            return new Response(status: 403);
        }

        $appliance = $this->security->getUser();
        if (!$appliance instanceof Appliance) {
            throw new \Exception('This route is only available to appliances');
        }

        $data = $request->request->all();
        $file = $request->files->get('file');

        $eventIri = $data['event'] ?? null;
        $takenAt = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data['taken_at'] ?? null);
        $unattended = $data['unattended'] ?? null;
        $applianceUuid = $data['appliance_uuid'] ?? null;


        if (!$eventIri || !$takenAt || !$unattended || !$applianceUuid || !$file) {
            return new Response(status: 400);
        }

        // @TODO: Ensure this is a valid uuid
        $applianceUuid = Uuid::fromString($applianceUuid);

        $alreadyUploadedPict = $this->pictureRepository->findOneBy(['applianceUuid' => $applianceUuid]);
        if ($alreadyUploadedPict) {
            return new JsonResponse($this->normalizer->normalize($alreadyUploadedPict, 'json', [
                'groups' => [Picture::API_GET_ITEM]
            ]));
        }

        $unattended = (bool)filter_var($unattended, FILTER_VALIDATE_BOOLEAN);

        try {
            $event = $this->iriConverter->getResourceFromIri($eventIri);
            if (!$event instanceof Event || $event->getOwner() !== $appliance->getOwner()) {
                throw new \Exception('Event not found');
            }
        } catch (\Exception $e) {
            return new JsonResponse(["message" => "Event not found for this owner"], status: 404);
        }

        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        $picture = (new Picture())
            ->setEvent($event)
            ->setTakenAt($takenAt)
            ->setUnattended($unattended)
            ->setFile($file)
            ->setApplianceUuid($applianceUuid);

        $this->emi->persist($picture);
        $this->emi->flush();

        return new JsonResponse($this->normalizer->normalize($picture, 'json', [
            'groups' => [Picture::API_GET_ITEM]
        ]));
    }
}
