pipeline {
    options {
        disableRestartFromStage()
    }

    agent none

    environment {
        PIPELINE_VERSION = util.pipelineVersion()
        REPOSITORY_NAME  = 'platform-api'
    }

    stages {
        stage('Pre build') {
            steps {
                setBuildDisplayName to: env.PIPELINE_VERSION
                sendBuildNotification()
            }
        }

        stage('Setup and build') {
            stages {
                stage('Build frontend') {
                    agent {
                        docker {
                            image 'base_images/node/22'
                            label 'docker'
                        }
                    }
                    environment {
                        npm_config_cache = '/tmp/.npm-cache'
                    }
                    steps {
                        script {
                            // Pins the acceptance tests' Playwright image to the version in the lockfile
                            env.PLAYWRIGHT_VERSION = readJSON(file: 'package-lock.json').packages['node_modules/@playwright/test'].version
                        }
                        sh label: 'Install node modules', script: 'npm ci'
                        sh label: 'Build frontend', script: 'npm run build'
                        sh label: 'Install production node modules', script: 'npm ci --omit=dev'
                        stash name: 'frontend', includes: 'public/build/**,node_modules/**'
                    }
                    post {
                        cleanup {
                            cleanWs()
                        }
                    }
                }

                stage('Build backend and artifact') {
                    agent { label 'ubuntu && 24.04 && php8.2' }
                    environment {
                        GIT_SHORT_COMMIT = util.shortCommitRef()
                        ARTIFACT_VERSION = "${env.PIPELINE_VERSION}" + '+sha.' + "${env.GIT_SHORT_COMMIT}"
                    }
                    steps {
                        sh label: 'Install rubygems', script: 'bundle install --deployment'
                        withCredentials([usernamePassword(credentialsId: 'nova.laravel.com', usernameVariable: 'USER', passwordVariable: 'PASSWORD')]) {
                            sh label: 'Build backend', script: 'bundle exec rake build_backend NOVA_USER=${USER} NOVA_LICENSE_KEY=${PASSWORD}'
                        }
                        unstash 'frontend'
                        sh label: 'Build artifact', script: "bundle exec rake build_artifact ARTIFACT_VERSION=${env.ARTIFACT_VERSION}"
                        archiveArtifacts artifacts: "pkg/*${env.ARTIFACT_VERSION}*.deb", onlyIfSuccessful: true
                    }
                    post {
                        cleanup {
                            cleanWs()
                        }
                    }
                }
            }
        }

        stage('Upload artifact') {
            agent any
            options { skipDefaultCheckout() }
            steps {
                copyArtifacts filter: 'pkg/*.deb', projectName: env.JOB_NAME, flatten: true, selector: specific(env.BUILD_NUMBER)
                uploadAptlyArtifacts artifacts: '*.deb', repository: env.REPOSITORY_NAME
                createAptlySnapshot name: "${env.REPOSITORY_NAME}-${env.PIPELINE_VERSION}", repository: env.REPOSITORY_NAME
            }
            post {
                cleanup {
                    cleanWs()
                }
            }
        }

        stage('Deploy to development') {
            agent any
            options { skipDefaultCheckout() }
            environment {
                APPLICATION_ENVIRONMENT = 'development'
            }
            steps {
                publishAptlySnapshot snapshotName: "${env.REPOSITORY_NAME}-${env.PIPELINE_VERSION}", publishTarget: "${env.REPOSITORY_NAME}-${env.APPLICATION_ENVIRONMENT}", distributions: 'focal'
            }
        }

        stage('Deploy to acceptance') {
            agent { label 'ubuntu && 24.04' }
            options { skipDefaultCheckout() }
            environment {
                APPLICATION_ENVIRONMENT = 'acceptance'
            }
            steps {
                publishAptlySnapshot snapshotName: "${env.REPOSITORY_NAME}-${env.PIPELINE_VERSION}", publishTarget: "${env.REPOSITORY_NAME}-${env.APPLICATION_ENVIRONMENT}", distributions: 'focal'
                triggerDeployment nodeName: 'platform-web-acc01'
            }
            post {
                always {
                    sendBuildNotification to: '#upw-ops', message: "Pipeline <${env.RUN_DISPLAY_URL}|${util.getJobDisplayName()} [${currentBuild.displayName}]>: deployed to *${env.APPLICATION_ENVIRONMENT}*"
                }
            }
        }

        stage('Acceptance tests') {
            agent {
                docker {
                    image "playwright:v${env.PLAYWRIGHT_VERSION}-noble"
                    registryUrl 'https://mcr.microsoft.com'
                    label 'container'
                    args  '-e HOME=/tmp'
                }
            }
            environment {
                E2E_TEST_BASE_URL      = 'https://platform-acc.publiq.be'
                KEYCLOAK_LOGIN_ENABLED = 'true'
            }
            stages {
                stage('Setup') {
                    steps {
                        sh label: 'Install dependencies', script: 'npm ci'
                    }
                }
                stage('Run acceptance tests') {
                    steps {
                        sendBuildNotification to: '#mwl-publiq-tests', message: "Pipeline <${env.RUN_DISPLAY_URL}|${util.getJobDisplayName()} [${currentBuild.displayName}]>: automated acceptance tests started"
                        withCredentials([usernamePassword(credentialsId: 'publiq-platform_e2etest_user',
                                                          usernameVariable: 'E2E_TEST_EMAIL',
                                                          passwordVariable: 'E2E_TEST_PASSWORD'),
                                         usernamePassword(credentialsId: 'publiq-platform_e2etest_v1',
                                                          usernameVariable: 'E2E_TEST_V1_EMAIL',
                                                          passwordVariable: 'E2E_TEST_V1_PASSWORD'),
                                         usernamePassword(credentialsId: 'publiq-platform_e2etest_admin',
                                                          usernameVariable: 'E2E_TEST_ADMIN_EMAIL',
                                                          passwordVariable: 'E2E_TEST_ADMIN_PASSWORD')]
                        ) {
                            catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
                                sh label: 'Run acceptance tests', script: 'npm run test:e2e'
                            }
                        }
                        archiveArtifacts artifacts: "screenshots/**/*", onlyIfSuccessful: false
                        archiveArtifacts artifacts: "test-results/**/*", onlyIfSuccessful: false
                    }
                    post {
                        always {
                            sendBuildNotification to: '#mwl-publiq-tests', message: "Pipeline <${env.RUN_DISPLAY_URL}|${util.getJobDisplayName()} [${currentBuild.displayName}]>: automated acceptance tests finished"
                        }
                    }
                }
            }
            post {
                cleanup {
                    cleanWs()
                }
            }
        }

        stage('Deploy to testing') {
            input { message "Deploy to Testing?" }
            agent { label 'ubuntu && 24.04' }
            options { skipDefaultCheckout() }
            environment {
                APPLICATION_ENVIRONMENT = 'testing'
            }

            steps {
                publishAptlySnapshot snapshotName: "${env.REPOSITORY_NAME}-${env.PIPELINE_VERSION}", publishTarget: "${env.REPOSITORY_NAME}-${env.APPLICATION_ENVIRONMENT}", distributions: 'focal'
                triggerDeployment nodeName: 'platform-web-test01'
            }
            post {
                always {
                    sendBuildNotification to: '#upw-ops', message: "Pipeline <${env.RUN_DISPLAY_URL}|${util.getJobDisplayName()} [${currentBuild.displayName}]>: deployed to *${env.APPLICATION_ENVIRONMENT}*"
                }
            }
        }

        stage('Deploy to production') {
            input { message "Deploy to Production?" }
            agent { label 'ubuntu && 24.04' }
            options { skipDefaultCheckout() }
            environment {
                APPLICATION_ENVIRONMENT = 'production'
            }

            steps {
                publishAptlySnapshot snapshotName: "${env.REPOSITORY_NAME}-${env.PIPELINE_VERSION}", publishTarget: "${env.REPOSITORY_NAME}-${env.APPLICATION_ENVIRONMENT}", distributions: 'focal'
                triggerDeployment nodeName: 'platform-web-prod01'
            }
            post {
                always {
                    sendBuildNotification to: '#upw-ops', message: "Pipeline <${env.RUN_DISPLAY_URL}|${util.getJobDisplayName()} [${currentBuild.displayName}]>: deployed to *${env.APPLICATION_ENVIRONMENT}*"
                }
                cleanup {
                    cleanupAptlySnapshots repository: env.REPOSITORY_NAME
                }
            }
        }

        stage('Tag release') {
            options { skipDefaultCheckout() }

            agent any
            steps {
                copyArtifacts filter: 'pkg/*.deb', projectName: env.JOB_NAME, flatten: true, selector: specific(env.BUILD_NUMBER)
                tagRelease commitHash: artifact.metadata(artifactFilter: '*.deb', field: 'git-ref')
            }
            post {
                cleanup {
                    cleanWs()
                }
            }
        }
    }

    post {
        always {
            sendBuildNotification()
        }
    }
}
